// Dashboard JS: fetch stats and render charts (Chart.js must be included in UI)
(async function(){
  try{
    const res = await fetch('/perpus_ai/public/api.php/api/dashboard');
    const data = await res.json();
    console.log('Dashboard data', data);
    // Example: render counts into elements if exist
    if (document.getElementById('total_books')) document.getElementById('total_books').innerText = data.stats.total_books;
    if (document.getElementById('total_members')) document.getElementById('total_members').innerText = data.stats.total_members;

    // Render popular books list if element exists
    if (document.getElementById('popular_books')){
      const el = document.getElementById('popular_books');
      el.innerHTML = data.popular_books.map(b => `<li>${b.title} (${b.borrow_count})</li>`).join('');
    }

    // Chart example
    if (window.Chart && document.getElementById('chartBooks')){
      const ctx = document.getElementById('chartBooks').getContext('2d');
      const labels = data.popular_books.map(b=>b.title);
      const values = data.popular_books.map(b=>b.borrow_count);
      new Chart(ctx, {type:'bar', data:{labels, datasets:[{label:'Borrow count', data:values, backgroundColor:'rgba(59,130,246,0.6)'}]}});
    }
  }catch(e){console.error(e)}
})();
