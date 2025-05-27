<?php include 'navigation.php'; ?>
<?php include 'db_connect.php'; ?>

<!-- Fetch Apartments from Database -->
<?php
$apartment_query = "
SELECT 
    a.*, 
    u.name AS landlord_name, 
    u.email AS landlord_email, 
    u.mobile AS landlord_mobile,
    ai.image_path,
    (
      SELECT COUNT(*) 
      FROM apartment_units 
      WHERE apartment_id = a.id AND unit_status = 'Available'
    ) AS available_unit_count
FROM apartments a
JOIN users u ON a.landlord_id = u.id
LEFT JOIN (
    SELECT apartment_id, MIN(uploaded_at) AS first_image_time
    FROM apartment_images
    GROUP BY apartment_id
) img_time ON img_time.apartment_id = a.id
LEFT JOIN apartment_images ai 
    ON ai.apartment_id = img_time.apartment_id AND ai.uploaded_at = img_time.first_image_time
ORDER BY 
  (
    SELECT COUNT(*) 
    FROM apartment_units au 
    WHERE au.apartment_id = a.id AND au.unit_status = 'Available'
  ) = 0 ASC,
  (
    SELECT COUNT(*) 
    FROM apartment_units au 
    WHERE au.apartment_id = a.id AND au.unit_status = 'Available'
  ) DESC;
";
$apartments = mysqli_query($conn, $apartment_query);
?>

<!-- TABS -->
<br><br><br><br>
<nav class="tab-nav">
    <button class="tab active" id="feed-tab">Feed</button>
    <button class="tab" id="map-tab">Map View</button>
</nav>

<style>
    .apartment-list {
        margin-top: 15px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .apartment-card {
        display: flex;
        align-items: center;
        background: #e0e0e0;
        border-radius: 10px;
        padding: 15px;
        gap: 15px;
        transition: 0.3s;
    }
    .apartment-card:hover {
        transform: scale(1.02);
        background-color: #d4d4d4;
    }
    .apartment-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .img-placeholder {
        width: 100px;
        height: 100px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f4f4f4;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .img-placeholder img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }
    .apartment-info {
        font-size: 14px;
        font-weight: bold;
        flex: 1;
    }
    .apartment-info h3 {
        font-size: 18px;
        margin-bottom: 5px;
        color: #007bff;
        font-weight: bold;
    }
    .apartment-price {
        font-size: 16px;
        font-weight: bold;
        color: black;
        margin-top: 5px;
    }
    .landlord-info {
        font-size: 12px;
        color: #555;
    }
    .filter-tags button, .price-range button {
        background: #f4f4f4;
        border: 1px solid #ccc;
        border-radius: 20px;
        padding: 8px 15px;
        margin: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.2s;
    }
    .filter-tags button.active, .price-range button.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    .apartment-card[data-hidden="true"] {
        display: none;
    }
</style>

<!-- MAIN CONTENT -->
<main class="container">

    <!-- FILTERS -->
    <section class="filters">
        <h2>Browse Type of Apartment</h2>
        <div class="filter-tags" id="typeFilters">
            <button data-type="Studio">Studio</button>
            <button data-type="Loft">Loft</button>
            <button data-type="Duplex">Duplex</button>
            <button data-type="Micro">Micro</button>
        </div>

        <h2>Price Range</h2>
        <div class="price-range" id="priceFilters">
            <button data-price="1-2000">Php 1,000 - 2,000</button>
            <button data-price="2100-3000">Php 2,100 - 3,000</button>
            <button data-price="3100-4000">Php 3,100 - 4,000</button>
            <button data-price="4100-5000">Php 4,100 - 5,000</button>
        </div>
    </section>

    <!-- APARTMENT LIST -->
    <section class="apartment-list" id="apartmentList">
        <?php while ($apartment = mysqli_fetch_assoc($apartments)): ?>
            <?php
                $apartment_id = $apartment['id'];

                $unit_count_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM apartment_units WHERE apartment_id = $apartment_id");
                $unit_count = mysqli_fetch_assoc($unit_count_query);

                $displayImage = (!empty($apartment['image_path']) && file_exists($apartment['image_path']))
                    ? $apartment['image_path']
                    : 'uploads/default_apartment.jpg';
            ?>
            <a href="apartment_details_public.php?id=<?= $apartment['id']; ?>" class="apartment-link">
                <div class="apartment-card"
                    data-type="<?= strtolower($apartment['apartment_type']); ?>"
                    data-price="<?= $apartment['price']; ?>">

                    <!-- Apartment Image -->
                    <div class="img-placeholder">
                        <img src="<?= $displayImage; ?>" alt="Apartment">
                    </div>

                    <!-- Apartment Details -->
                    <div class="apartment-info">
                        <h3><?= htmlspecialchars($apartment['name']); ?></h3>
                        <p class="landlord-info"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($apartment['location']); ?></p>
                        <p class="apartment-price">₱ <?= number_format($apartment['price'], 2); ?> / month</p>
                        <p class="landlord-info"><i class="fas fa-home"></i> Type: <?= htmlspecialchars($apartment['apartment_type']); ?></p>
                        <p class="landlord-info"><i class="fas fa-door-open"></i> Total Units: <?= $unit_count['total']; ?></p>
                        <br>
                        <p class="landlord-info"><i class="fas fa-door-open"></i> Available Units: <?= $apartment['available_unit_count']; ?></p>
                    </div>

                </div>
            </a>
        <?php endwhile; ?>
    </section>

</main>

<!-- JavaScript for Navigation -->
<script>
    document.getElementById("map-tab").addEventListener("click", function () {
        window.location.href = "mapview.php";
    });
    document.getElementById("feed-tab").addEventListener("click", function () {
        window.location.href = "index.php";
    });

    const typeButtons = document.querySelectorAll('#typeFilters button');
    const priceButtons = document.querySelectorAll('#priceFilters button');
    const apartments = document.querySelectorAll('.apartment-card');

    let activeType = '';
    let activePrice = '';

    function filterApartments() {
        apartments.forEach(card => {
            const type = card.dataset.type;
            const price = parseFloat(card.dataset.price);

            const matchesType = !activeType || type === activeType.toLowerCase();
            const matchesPrice = !activePrice || (
                price >= parseInt(activePrice.split('-')[0]) &&
                price <= parseInt(activePrice.split('-')[1])
            );

            card.dataset.hidden = !(matchesType && matchesPrice);
        });
    }

    typeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            typeButtons.forEach(b => b.classList.remove('active'));
            if (activeType === btn.dataset.type) {
                activeType = '';
            } else {
                activeType = btn.dataset.type;
                btn.classList.add('active');
            }
            filterApartments();
        });
    });

    priceButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            priceButtons.forEach(b => b.classList.remove('active'));
            if (activePrice === btn.dataset.price) {
                activePrice = '';
            } else {
                activePrice = btn.dataset.price;
                btn.classList.add('active');
            }
            filterApartments();
        });
    });
</script>
