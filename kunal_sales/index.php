<?php
include 'header.php';
include 'config/db.php';

$categories = mysqli_query($conn, "
    SELECT category_id, category_name
    FROM categories
    WHERE status = 1
      AND (
        LOWER(category_name) LIKE '%inverter%'
        OR LOWER(category_name) LIKE '%battery%'
        OR LOWER(category_name) LIKE '%ro%'
        OR LOWER(category_name) LIKE '%water%'
      )
    ORDER BY category_id ASC
");
?>
<main>
<section class="hero-advanced">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-kicker">POWER FOR EVERY MOMENT · WATER FOR EVERY DAY</span>
        <h1>Power your world.<br><span>Pure your water.</span></h1>
        <p>Discover dependable inverters, long-lasting batteries and advanced RO water purifiers — thoughtfully selected for your home.</p>
        <div class="hero-actions">
            <a href="pages/products.php" class="hero-main-btn">Explore the collection <span aria-hidden="true">↗</span></a>
            <a href="#collections" class="hero-secondary-btn">Discover categories</a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat"><strong>POWER</strong><span>Inverters &amp; batteries</span></div>
            <div class="hero-stat"><strong>PURE</strong><span>RO &amp; water purifiers</span></div>
        </div>
    </div>
    <div class="hero-visual" aria-hidden="true">
        <div class="hero-orbit"></div>
        <div class="hero-product-art">
            <div class="art-main"><strong>ϟ</strong><small>POWER · PURE · HOME</small></div>
            <div class="art-base"></div>
        </div>
    </div>
    <div class="hero-glow"></div>
</section>

<section class="section-shell" id="collections">
    <div class="section-heading">
        <div><span class="eyebrow">THE KUNAL COLLECTION</span><h2>Two essentials. One better home.</h2><p>Explore the products that keep your home powered and your water pure.</p></div>
        <a class="text-link" href="pages/products.php">View all products ↗</a>
    </div>
</section>

<section class="categories" aria-label="Product categories">
<?php
$index = 1;
if ($categories && mysqli_num_rows($categories) > 0):
    while ($cat = mysqli_fetch_assoc($categories)):
        $name = $cat['category_name'];
        $lowerName = strtolower($name);
        $description = (strpos($lowerName, 'inverter') !== false)
            ? 'Reliable backup power when you need it most.'
            : ((strpos($lowerName, 'battery') !== false)
                ? 'Dependable energy storage for everyday life.'
                : 'Advanced filtration for cleaner, better-tasting water.');
?>
    <a href="pages/products.php?category=<?= (int)$cat['category_id'] ?>" class="card">
        <span class="cat-index"><?= str_pad((string)$index, 2, '0', STR_PAD_LEFT) ?></span>
        <span class="eyebrow"><?= (strpos($lowerName, 'ro') !== false || strpos($lowerName, 'water') !== false) ? 'WATER SOLUTIONS' : 'POWER SOLUTIONS' ?></span>
        <h3><?= htmlspecialchars($name) ?></h3>
        <p><?= htmlspecialchars($description) ?></p>
    </a>
<?php
        $index++;
    endwhile;
else:
?>
    <div class="card"><h3>Products coming soon</h3><p>Our power and water collections will appear here.</p></div>
<?php endif; ?>
</section>

<section class="section-shell">
    <div class="section-heading">
        <div><span class="eyebrow">MADE FOR YOUR EVERYDAY</span><h2>Power. Pure. Peace of mind.</h2><p>Focused product collections for two important needs at home.</p></div>
    </div>
    <div class="row g-3">
        <div class="col-md-6"><div class="category-tile"><span class="eyebrow">01 / POWER</span><h3 class="mt-3" style="font-family:'Space Grotesk';font-size:28px">Stay powered, uninterrupted.</h3><p class="mb-4" style="color:var(--muted)">Explore inverters and batteries built for dependable backup.</p><a class="text-link" href="pages/products.php">Shop power solutions ↗</a></div></div>
        <div class="col-md-6"><div class="category-tile"><span class="eyebrow">02 / WATER</span><h3 class="mt-3" style="font-family:'Space Grotesk';font-size:28px">Make every drop count.</h3><p class="mb-4" style="color:var(--muted)">Find RO systems and water purifiers for your home.</p><a class="text-link" href="pages/products.php?category=5">Shop water solutions ↗</a></div></div>
    </div>
</section>
</main>
<?php include 'footer.php'; ?>