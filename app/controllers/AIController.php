<?php
require_once __DIR__ . '/../models/Database.php';

class AIController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    // Very small TF-IDF + Cosine similarity search over nlp_dataset
    public function buildIndex(){
        $rows = $this->db->query('SELECT id, text FROM nlp_dataset')->fetchAll();
        $docs = [];
        foreach ($rows as $r) $docs[$r['id']] = $this->tokenize($r['text']);

        $df = [];
        foreach ($docs as $id => $tokens) {
            $unique = array_count_values($tokens);
            foreach ($unique as $t => $c) $df[$t] = ($df[$t] ?? 0) + 1;
        }
        $N = count($docs);
        $index = [];
        foreach ($docs as $id => $tokens) {
            $tf = array_count_values($tokens);
            $vec = [];
            foreach ($tf as $t => $f) {
                $idf = log(($N+1)/($df[$t]??1));
                $vec[$t] = $f * $idf;
            }
            // store vector as json
            $this->db->prepare('INSERT INTO ai_index (dataset_id, vector, created_at) VALUES (?, ?, NOW())')
                ->execute([$id, json_encode($vec)]);
        }
        return true;
    }

    public function search($query){
        $qtokens = $this->tokenize($query);
        $qtf = array_count_values($qtokens);
        $rows = $this->db->query('SELECT ai.dataset_id, ai.vector, d.text FROM ai_index ai JOIN nlp_dataset d ON d.id=ai.dataset_id')->fetchAll();
        $results = [];
        foreach ($rows as $r) {
            $vec = json_decode($r['vector'], true);
            $score = $this->cosine($vec, $qtf);
            $results[] = ['id' => $r['dataset_id'], 'text' => $r['text'], 'score' => $score];
        }
        usort($results, function($a,$b){return $b['score']<=>$a['score'];});
        return array_slice($results,0,10);
    }

    private function tokenize($text){
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/u', ' ', $text);
        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        // remove stopwords
        $sw = $this->db->query('SELECT word FROM stopwords')->fetchAll(PDO::FETCH_COLUMN);
        $tokens = array_values(array_filter($tokens, function($t) use ($sw){ return !in_array($t, $sw); }));
        return $tokens;
    }

    private function cosine($vecA, $vecB){
        // vecA is assoc term=>weight, vecB is term=>tf
        $dot = 0.0; $na = 0.0; $nb = 0.0;
        foreach ($vecA as $t => $w) { $na += $w*$w; $b = $vecB[$t] ?? 0; $dot += $w * $b; }
        foreach ($vecB as $b) $nb += $b*$b;
        if ($na==0 || $nb==0) return 0;
        return $dot / (sqrt($na) * sqrt($nb));
    }
}
