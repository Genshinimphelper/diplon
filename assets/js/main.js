// main.js
document.addEventListener('DOMContentLoaded', () => {

  // Elements
  const filterForm = document.getElementById('filterForm');
  const resultsContainer = document.getElementById('filterResults');
  const spinner = document.getElementById('spinner');

  // debounce helper
  function debounce(fn, delay=300){
    let t;
    return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this, args), delay); };
  }

  // collect form values
  function getParams(){
    const params = new URLSearchParams();
    const brand = document.getElementById('brand').value;
    const cat = document.getElementById('category').value;
    const price_min = document.getElementById('price_min').value;
    const price_max = document.getElementById('price_max').value;
    const sort = document.getElementById('sort').value;
    const q = document.getElementById('search_text').value;

    if (brand) params.append('brand', brand);
    if (cat) params.append('category', cat);
    if (price_min) params.append('price_min', price_min);
    if (price_max) params.append('price_max', price_max);
    if (sort) params.append('sort', sort);
    if (q) params.append('q', q.trim());
    return params.toString();
  }

  async function loadResults(){
    const params = getParams();
    spinner.style.display = 'block';
    try {
      const res = await fetch('ajax_search.php?' + params);
      if (!res.ok) throw new Error('Ошибка загрузки');
      const html = await res.text();
      resultsContainer.innerHTML = html;
    } catch (e) {
      resultsContainer.innerHTML = '<p class="error">Ошибка загрузки результатов</p>';
      console.error(e);
    } finally {
      spinner.style.display = 'none';
    }
  }

  if (filterForm) {
    const deb = debounce(loadResults, 350);
    filterForm.addEventListener('change', deb);
    document.getElementById('search_text').addEventListener('input', deb);
    // initial load
    loadResults();
  }

  // show more brands
  const showBtn = document.querySelector('.show-more-brands');
  const brandCards = document.querySelectorAll('.brand-card');
  const maxVisible = 15;
  if (brandCards.length > maxVisible) {
    brandCards.forEach((c, i) => { if (i >= maxVisible) c.style.display = 'none'; });
    if (showBtn) {
      showBtn.addEventListener('click', () => {
        brandCards.forEach(c => c.style.display = 'block');
        showBtn.style.display = 'none';
      });
    }
  } else {
    if (showBtn) showBtn.style.display = 'none';
  }

  // new arrivals slider
  const newContainer = document.querySelector('.new-container');
  const prevNew = document.querySelector('.prev-new');
  const nextNew = document.querySelector('.next-new');
  if (newContainer && prevNew && nextNew) {
    const gap = parseInt(getComputedStyle(newContainer).gap) || 15;
    prevNew.addEventListener('click', () => {
      const card = newContainer.querySelector('.new-card');
      const w = card ? card.offsetWidth + gap : 240;
      newContainer.scrollBy({ left: -w, behavior: 'smooth' });
    });
    nextNew.addEventListener('click', () => {
      const card = newContainer.querySelector('.new-card');
      const w = card ? card.offsetWidth + gap : 240;
      newContainer.scrollBy({ left: w, behavior: 'smooth' });
    });
  }

  // marquee animation
  const marquee = document.querySelector('.marquee-content');
  if (marquee) {
    let pos = 0;
    const speed = 0.5;
    function tick(){
      pos += speed;
      if (pos >= marquee.scrollWidth / 2) pos = 0;
      marquee.style.transform = `translateX(-${pos}px)`;
      requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

});
