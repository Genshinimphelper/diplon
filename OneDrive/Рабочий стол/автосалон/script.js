document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Анимация появления элементов при скролле (Scroll Reveal)
    const observerOptions = { threshold: 0.1 };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('section, .car-card, .stat-node').forEach(el => {
        el.classList.add('scroll-reveal');
        observer.observe(el);
    });

    // 2. Инженерный Кредитный Калькулятор
    const priceRange = document.getElementById('price');
    const depositRange = document.getElementById('deposit');
    const monthDisplay = document.getElementById('month');
    const applyBtn = document.getElementById('apply-btn');

    if (priceRange && depositRange) {
        const updateCalc = () => {
            const p = parseInt(priceRange.value);
            const d = parseInt(depositRange.value);
            
            // Обновляем текстовые значения под ползунками
            document.getElementById('p-val').innerText = p.toLocaleString();
            document.getElementById('d-val').innerText = d.toLocaleString();

            // Логика: ставка 12%, срок 60 месяцев
            const loan = p - d;
            const monthly = loan > 0 ? Math.round((loan * 0.12) / 12 + (loan / 60)) : 0;
            
            monthDisplay.innerText = monthly.toLocaleString();
            
            // Обновляем ссылку на оформление, чтобы передать сумму
            if (applyBtn) {
                applyBtn.href = `credit_apply.php?amount=${loan}`;
            }
        };

        priceRange.addEventListener('input', updateCalc);
        depositRange.addEventListener('input', updateCalc);
        updateCalc(); // Инициализация при загрузке
    }

    // 3. Таймер исчезновения индикатора прокрутки (мышки)
    const scrollIcon = document.querySelector('.scroll-indicator');
    if (scrollIcon) {
        setTimeout(() => {
            scrollIcon.style.opacity = '0';
            setTimeout(() => scrollIcon.remove(), 1000);
        }, 5000);
    }

    // 4. Эффект параллакса для Hero заголовка
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const heroTitle = document.querySelector('.banner h1');
        if (heroTitle) {
            heroTitle.style.transform = `translateY(${scrolled * 0.3}px)`;
            heroTitle.style.opacity = 1 - (scrolled / 600);
        }
    });

    // 5. Галерея: Плавная смена фото
    const stageImg = document.getElementById('stage-img');
    const thumbnails = document.querySelectorAll('.thumb-card');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const newSrc = this.querySelector('img').src;
            stageImg.style.opacity = '0';
            setTimeout(() => {
                stageImg.src = newSrc;
                stageImg.style.opacity = '1';
            }, 200);
        });
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const themeBtn = document.getElementById('theme-toggle');
    const htmlEl = document.documentElement;

    // Список состояний: null (системная), light, dark
    const themeCycle = [null, 'light', 'dark'];

    // Определяем текущий индекс на основе того, что сохранено
    let currentTheme = localStorage.getItem('theme'); // может быть null, 'light' или 'dark'
    let currentIndex = themeCycle.indexOf(currentTheme);
    if (currentIndex === -1) currentIndex = 0;

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            // Переходим к следующей теме в цикле
            currentIndex = (currentIndex + 1) % themeCycle.length;
            const nextTheme = themeCycle[currentIndex];

            if (nextTheme) {
                // Устанавливаем принудительную тему
                htmlEl.setAttribute('data-theme', nextTheme);
                localStorage.setItem('theme', nextTheme);
            } else {
                // Сбрасываем на авто (удаляем настройки)
                htmlEl.removeAttribute('data-theme');
                localStorage.removeItem('theme');
            }
            
            console.log('Theme changed to:', nextTheme || 'System Auto');
        });
    }
});

    // 2. Favorites AJAX
// Favorites AJAX Logic
document.querySelectorAll('.fav-btn-ajax').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        const carId = btn.dataset.id;
        const label = btn.querySelector('.fav-label');
        const formData = new FormData();
        formData.append('car_id', carId);

        try {
            const resp = await fetch('toggle_favorite.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const res = await resp.text();
            
            if (res === 'added') {
                btn.classList.add('active');
                if (label) label.innerText = "В ИЗБРАННОМ // SAVED"; // Можно использовать значения из lang.php
            } else if (res === 'removed') {
                btn.classList.remove('active');
                if (label) label.innerText = "В ИЗБРАННОЕ // SAVE";
            } else if (res === 'auth_required') {
                window.location.href = 'login.php';
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
        }
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const priceRange = document.getElementById('price');
    const depositRange = document.getElementById('deposit');
    const monthDisplay = document.getElementById('month');
    const pValDisplay = document.getElementById('p-val');
    const dValDisplay = document.getElementById('d-val');
    const applyBtn = document.getElementById('apply-btn');

    if (priceRange && depositRange) {
        const updateCalc = () => {
            const p = parseInt(priceRange.value);
            const d = parseInt(depositRange.value);
            
            pValDisplay.innerText = p.toLocaleString();
            dValDisplay.innerText = d.toLocaleString();

            const loan = p - d;
            // Ставка 12% годовых, срок 60 мес.
            const monthly = loan > 0 ? Math.round((loan * 0.12) / 12 + (loan / 60)) : 0;
            
            monthDisplay.innerText = monthly.toLocaleString();
            if (applyBtn) applyBtn.href = `credit_apply.php?amount=${loan}`;
        };

        priceRange.addEventListener('input', updateCalc);
        depositRange.addEventListener('input', updateCalc);
        updateCalc();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const mainImg = document.getElementById('main-gallery-img');
    const thumbs = document.querySelectorAll('.thumb-node');
    const nextBtn = document.getElementById('next-img');
    const prevBtn = document.getElementById('prev-img');

    if (mainImg && thumbs.length > 0) {
        let currentIndex = 0;

        const updateGallery = (index) => {
            currentIndex = index;
            // Плавная смена через прозрачность
            mainImg.style.opacity = '0.3';
            setTimeout(() => {
                mainImg.src = thumbs[currentIndex].dataset.src;
                mainImg.style.opacity = '1';
            }, 150);

            // Обновляем активную рамку у миниатюр
            thumbs.forEach(t => t.classList.remove('active'));
            thumbs[currentIndex].classList.add('active');
        };

        // Клик по миниатюре
        thumbs.forEach((thumb, i) => {
            thumb.addEventListener('click', () => updateGallery(i));
        });

        // Кнопка Вперед
        nextBtn.addEventListener('click', () => {
            let next = (currentIndex + 1) % thumbs.length;
            updateGallery(next);
        });

        // Кнопка Назад
        prevBtn.addEventListener('click', () => {
            let prev = (currentIndex - 1 + thumbs.length) % thumbs.length;
            updateGallery(prev);
        });

        // Поддержка переключения стрелками клавиатуры
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') nextBtn.click();
            if (e.key === 'ArrowLeft') prevBtn.click();
        });
    }
});

document.querySelectorAll('.compare-btn-ajax').forEach(btn => {
    btn.onclick = async (e) => {
        e.preventDefault();
        const fd = new FormData(); fd.append('car_id', btn.dataset.id);
        const r = await fetch('toggle_compare.php', { method: 'POST', body: fd });
        const res = await r.text();
        if (res === 'added') btn.classList.add('active');
        else if (res === 'removed') btn.classList.remove('active');
        else if (res === 'limit_reached') alert('Максимум 4 автомобиля');
    };
});

document.querySelectorAll('.compare-btn-ajax').forEach(btn => {
    btn.onclick = async (e) => {
        e.preventDefault();
        const carId = btn.dataset.id;
        const badge = document.getElementById('compare-count-badge');

        const fd = new FormData(); 
        fd.append('car_id', carId);

        try {
            const r = await fetch('toggle_compare.php', { method: 'POST', body: fd });
            const res = await r.text();
            
            if (res === 'added' || res === 'removed') {
                btn.classList.toggle('active');
                
                // АНИМАЦИЯ СЧЕТЧИКА:
                // Мы просто запрашиваем обновленное количество сессии (или считаем на фронте)
                // Для простоты в этом проекте, просто обновим число:
                let currentCount = parseInt(badge.innerText);
                if (res === 'added') currentCount++;
                else currentCount--;
                
                badge.innerText = currentCount;
                
                // Добавляем эффект "прыжка"
                badge.classList.add('bump');
                setTimeout(() => badge.classList.remove('bump'), 300);
            } else if (res === 'limit_reached') {
                alert('LIMIT: MAX 4 UNITS');
            }
        } catch (err) {
            console.error(err);
        }
    };
});