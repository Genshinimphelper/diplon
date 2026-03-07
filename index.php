<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';

$brands_list = pg_fetch_all(pg_query($conn, "SELECT id, name FROM brands ORDER BY name")) ?: [];
$pop_res = pg_query($conn, "SELECT c.*, b.name AS brand FROM cars c LEFT JOIN brands b ON b.id = c.brand_id WHERE c.status = 'active' ORDER BY c.views DESC LIMIT 4");
$popular_cars = pg_fetch_all($pop_res) ?: [];

require_once 'header.php';
?>

<main>
    <!-- HERO -->
    <section class="banner">
        <div class="wrap">
            <h1><?= $txt['hero_1'] ?> <span><?= $txt['hero_2'] ?></span> <?= $txt['hero_3'] ?></h1>
            <p><?= $txt['hero_p'] ?></p>
            <div class="quick-filter-wrapper">
                <div class="quick-filter">
                    <form action="catalog.php" method="GET" class="qf-form-industrial">
                        <div class="qf-group">
                            <label><?= $txt['f_brand'] ?></label>
                            <select name="brand">
                                <option value="0"><?= $txt['f_any'] ?></option>
                                <?php foreach ($brands_list as $b): ?><option value="<?= $b['id'] ?>"><?= $b['name'] ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="qf-group">
                            <label><?= $txt['f_price'] ?></label>
                            <input type="number" name="price_max" placeholder="0.00">
                        </div>
                        <button type="submit" class="btn-search-industrial"><?= $txt['btn_search'] ?></button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS -->
<section class="stats-stripe">
    <div class="wrap stats-grid">
        <!-- Блок 1 -->
        <div class="stat-node">
            <span class="stat-index"></span>
            <div class="stat-title"><?= $txt['st_1_h'] ?></div>
            <p class="stat-text"><?= $txt['st_1_p'] ?></p>
        </div>
        <!-- Блок 2 -->
        <div class="stat-node">
            <span class="stat-index"></span>
            <div class="stat-title"><?= $txt['st_2_h'] ?></div>
            <p class="stat-text"><?= $txt['st_2_p'] ?></p>
        </div>
        <!-- Блок 3 -->
        <div class="stat-node">
            <span class="stat-index"></span>
            <div class="stat-title"><?= $txt['st_3_h'] ?></div>
            <p class="stat-text"><?= $txt['st_3_p'] ?></p>
        </div>
        <!-- Блок 4 -->
        <div class="stat-node">
            <span class="stat-index"></span>
            <div class="stat-title"><?= $txt['st_4_h'] ?></div>
            <p class="stat-text"><?= $txt['st_4_p'] ?></p>
        </div>
    </div>
</section>

    <!-- POPULAR -->
    <section class="section-cars">
        <div class="wrap">
            <div class="section-header"><h2><?= $txt['sec_popular'] ?></h2><a href="catalog.php" class="view-all-link"><?= $txt['btn_all'] ?></a></div>
            <div class="car-grid">
                <?php foreach ($popular_cars as $car) include 'car_card_template.php'; ?>
            </div>
        </div>
    </section>

    <!-- FORMS -->
    <!-- СЕКЦИЯ ФОРМ (TRADE-IN И ТЕСТ-ДРАЙВ) -->
<section class="wrap">
    <div class="industrial-grid-2">
        
        <!-- TRADE-IN -->
        <div class="form-card-industrial">
            <div class="card-tag"></div>
            <h2><?= $txt['form_tradein_h'] ?></h2>
            <form action="process_leads.php?type=evaluate" method="POST" class="inner-form-industrial">
                <div class="form-group-industrial">
                    <label><?= $txt['ph_your_car'] ?></label>
                    <input type="text" name="car_info" placeholder="BRAND / MODEL" required>
                </div>
                
                <div class="form-row-2">
                    <div class="form-group-industrial">
                        <label><?= $txt['ph_year'] ?></label>
                        <input type="number" name="year" placeholder="20XX" required>
                    </div>
                    <div class="form-group-industrial">
                        <label><?= $txt['ph_km'] ?></label>
                        <input type="number" name="mileage" placeholder="KM" required>
                    </div>
                </div>

                <div class="form-group-industrial">
                    <label><?= $txt['ph_phone'] ?></label>
                    <input type="text" name="phone" placeholder="+7 --- --- -- --" required>
                </div>
                
                <button type="submit" class="btn-industrial-full"><?= $txt['btn_send'] ?></button>
            </form>
        </div>

        <!-- TEST DRIVE -->
        <div class="form-card-industrial">
            <div class="card-tag"></div>
            <h2><?= $txt['form_testdrive_h'] ?></h2>
            <form action="process_leads.php?type=testdrive" method="POST" class="inner-form-industrial">
                <div class="form-group-industrial">
                    <label><?= $txt['ph_select_car'] ?></label>
                    <select name="car_id" required>
                        <option value="" disabled selected>-</option>
                        <?php 
                        $all_cars = pg_query($conn, "SELECT c.id, b.name as brand, c.model FROM cars c JOIN brands b ON b.id = c.brand_id WHERE status='active'");
                        while($ac = pg_fetch_assoc($all_cars)) echo "<option value='{$ac['id']}'>{$ac['brand']} {$ac['model']}</option>";
                        ?>
                    </select>
                </div>
                
                <div class="form-group-industrial">
                    <label>Дата</label>
                    <input type="date" name="drive_date" required>
                </div>

                <div class="form-group-industrial">
                    <label><?= $txt['ph_phone'] ?></label>
                    <input type="text" name="phone" placeholder="+7 --- --- -- --" required>
                </div>
                
                <button type="submit" class="btn-industrial-full"><?= $txt['btn_book'] ?></button>
            </form>
        </div>

    </div>
</section>

    <!-- CALC -->
    <section class="wrap">
        <div class="calc-panel-refined">
            <div class="calc-inputs-side">
                <div class="card-tag"> </div>
                <h2><?= $txt['calc_h'] ?></h2>
                <div style="margin-top:40px;">
                    <label><?= $txt['calc_p'] ?>: <span id="p-val">2 500 000</span> ₽</label>
                    <input type="range" id="price" min="500000" max="10000000" step="50000" value="2500000">
                    <label><?= $txt['calc_d'] ?>: <span id="d-val">500 000</span> ₽</label>
                    <input type="range" id="deposit" min="0" max="5000000" step="50000" value="500000">
                </div>
            </div>
            <div class="calc-result-side">
                <span style="font-size:0.7rem; color:var(--text-muted);"><?= $txt['calc_m'] ?></span>
                <div class="result-amount" id="month">35 400</div>
                <a href="credit_apply.php" id="apply-btn" class="btn-industrial-full" style="text-decoration:none; margin-top:20px;">REQUEST</a>
            </div>
        </div>
    </section>

    <!-- PROTOCOL -->
<section class="wrap section-protocol-industrial">
    <div class="protocol-header-refined">
        <h2><?= $txt['sec_protocol'] ?></h2>
        
        <!-- Контейнер бегущей строки -->
        <div class="status-marquee">
            <div class="marquee-content">
                <span>STATUS: READY 100% OK SYSTEM STABLE NO ERRORS FOUND SCANNING COMPLETE </span>
                <span>STATUS: READY 100% OK SYSTEM STABLE NO ERRORS FOUND SCANNING COMPLETE </span>
            </div>
        </div>
    </div>
    <div class="protocol-grid-industrial">
    <?php for($i=1; $i<=4; $i++): ?>
    <div class="prot-box">
        <span class="prot-code">CODE: 0<?= $i ?>  AUDIT_SUCCESS</span>
        <h3><?= $txt["prot_{$i}_h"] ?></h3>
        <p><?= $txt["prot_{$i}_p"] ?></p>
    </div>
    <?php endfor; ?>
</div>
    </section>
</main>

<?php require_once 'footer.php'; ?>