<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['apartment_id']) || empty($_GET['apartment_id'])) {
    echo "<script>alert('Invalid Apartment!'); window.location.href='index.php';</script>";
    exit();
}

$apartment_id = intval($_GET['apartment_id']);

// Fetch the apartment
$apartment_query = "SELECT * FROM apartments WHERE id = ?";
$stmt = $conn->prepare($apartment_query);
$stmt->bind_param("i", $apartment_id);
$stmt->execute();
$apartment_result = $stmt->get_result();
$apartment = $apartment_result->fetch_assoc();

if (!$apartment) {
    echo "<script>alert('Apartment not found!'); window.location.href='index.php';</script>";
    exit();
}

// Fetch unit info with landlord info
$query = "SELECT u.*, l.name AS landlord_name, l.email AS landlord_email, l.mobile AS landlord_mobile 
          FROM apartment_units u
          JOIN apartments a ON u.apartment_id = a.id
          JOIN users l ON a.landlord_id = l.id
          WHERE u.apartment_id = ?
          ORDER BY u.unit_number ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $apartment_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if tenant already has a unit
$has_unit = false;
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'Tenant') {
    $tenant_id = $_SESSION['user_id'];
    $check_unit = $conn->prepare("SELECT id FROM tenant_rentals WHERE tenant_id = ? AND status = 'Active'");
    $check_unit->bind_param("i", $tenant_id);
    $check_unit->execute();
    $unit_result = $check_unit->get_result();
    $has_unit = $unit_result->num_rows > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Units - <?php echo htmlspecialchars($apartment['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f4f4f4; color: #333; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); }
        h2 { color: #007bff; margin-bottom: 15px; text-align: center; }
        .unit-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 20px; }

        .unit-card { background: white; padding: 15px; border-radius: 10px; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; align-items: center; transition: 0.3s; border: 1px solid #ddd; }
        .unit-card:hover { box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2); }

        .slider-container { width: 100%; border-radius: 8px; position: relative; max-width: 500px; overflow: hidden; }
        .slider-wrapper { display: flex; transition: transform 0.5s ease-in-out; }
        .slider-wrapper img { width: 100%; height: 250px; object-fit: cover; border-radius: 8px; cursor: pointer; }

        .slider-controls { display: flex; justify-content: center; gap: 10px; margin-top: 8px; }
        .slider-btn { padding: 6px 10px; background-color: #007bff; border: none; border-radius: 5px; color: white; cursor: pointer; font-size: 14px; }
        .slider-btn:hover { background-color: #0056b3; }

        .slider-dots { display: flex; justify-content: center; margin-top: 6px; }
        .dot { height: 10px; width: 10px; margin: 0 3px; background-color: #bbb; border-radius: 50%; display: inline-block; }
        .dot.active { background-color: #007bff; }

        .unit-info { width: 100%; padding: 10px; text-align: left; }
        .unit-info h3 { font-size: 20px; color: #007bff; font-weight: bold; margin-bottom: 5px; }
        .unit-price { font-size: 18px; font-weight: bold; color: black; margin-top: 5px; }
        .unit-status { font-size: 16px; font-weight: bold; text-transform: uppercase; }

        .unit-features { margin-top: 10px; }
        .feature-list { list-style: none; padding-left: 0; margin-top: 5px; }
        .feature-list li { font-size: 14px; margin-bottom: 6px; color: #333; display: flex; align-items: center; }
        .feature-list li i { color: #28a745; margin-right: 8px; }

        .landlord-info { background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #ddd; margin-top: 10px; text-align: center; }

        .btn { padding: 10px 14px; border: none; border-radius: 6px; color: white; font-size: 16px; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; margin-top: 10px; }
        .btn-contact { background: #28a745; }
        .btn-contact:hover { background: #218838; }
        .btn-map { background: #007bff; }
        .btn-map:hover { background: #0056b3; }
        .btn-rent { background: #ff9800; }
        .btn-rent:hover { background: #e68900; }
        .btn-disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="back">
  <a href="apartment_details.php?id=<?php echo $apartment_id; ?>" class="back-btn" title="Go back to apartment details">
    <i class="fa-solid fa-arrow-left"></i>
  </a>
</div>
<br>
<h2>Units at <?php echo htmlspecialchars($apartment['name']); ?></h2>

<div class="unit-list">
    <?php while ($unit = mysqli_fetch_assoc($result)): ?>
        <?php
            $image_query = "SELECT image_path FROM unit_images WHERE unit_id = ?";
            $stmt = $conn->prepare($image_query);
            $stmt->bind_param("i", $unit['id']);
            $stmt->execute();
            $image_result = $stmt->get_result();
            $images = [];
            while ($img = $image_result->fetch_assoc()) {
                $images[] = $img['image_path'];
            }
            if (empty($images)) {
                $images[] = "uploads/units/default_unit.jpg";
            }
        ?>

        <div class="unit-card">
            <div class="slider-container">
                <div class="slider-wrapper" id="slider-<?php echo $unit['id']; ?>">
                    <?php foreach ($images as $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" onclick="openImage('<?php echo htmlspecialchars($img); ?>')" alt="Unit Image">
                    <?php endforeach; ?>
                </div>
                <div class="slider-controls">
                    <button class="slider-btn" onclick="changeSlide('<?php echo $unit['id']; ?>', -1)">&#10094; Prev</button>
                    <button class="slider-btn" onclick="changeSlide('<?php echo $unit['id']; ?>', 1)">Next &#10095;</button>
                </div>
                <div class="slider-dots" id="dots-<?php echo $unit['id']; ?>"></div>
            </div>

            <div class="unit-info">
                <h3>Unit <?php echo htmlspecialchars($unit['unit_number']); ?></h3>
                <p class="unit-price">₱<?php echo number_format($unit['unit_price'], 2); ?> / month</p>
                <p class="unit-status <?php echo strtolower($unit['unit_status']); ?>">
                    <?php echo $unit['unit_status'] === 'Available' ? '✅ Available' : ($unit['unit_status'] === 'Pending' ? '🕐 Pending Approval' : '🏠 Occupied'); ?>
                </p>
                <div class="unit-features">
                    <p><strong>Features:</strong></p>
                    <ul class="feature-list">
                        <?php
                        $decoded = stripcslashes($unit['unit_features']);
                        $feature_lines = preg_split("/\\r\\n|\\r|\\n/", $decoded);
                        foreach ($feature_lines as $feature):
                            if (trim($feature) !== ''):
                        ?>
                        <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="landlord-info">
                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($unit['landlord_name']); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($unit['landlord_email']); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($unit['landlord_mobile']); ?></p>

                <?php if ($unit['unit_status'] === 'Available'): ?>
                    <?php if ($has_unit): ?>
                        <button class="btn btn-disabled" disabled><i class="fas fa-ban"></i> Already Renting</button>
                    <?php else: ?>
                        <a href="rent.php?id=<?php echo $unit['id']; ?>" class="btn btn-rent"><i class="fas fa-home"></i> Rent Now</a>
                    <?php endif; ?>
                <?php elseif ($unit['unit_status'] === 'Pending'): ?>
                    <button class="btn btn-disabled" disabled><i class="fas fa-clock"></i> Waiting Approval</button>
                <?php else: ?>
                    <button class="btn btn-disabled" disabled><i class="fas fa-lock"></i> Occupied</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
function openImage(imgSrc) {
    window.open(imgSrc, "_blank");
}

const sliders = {};

document.querySelectorAll('.slider-wrapper').forEach(wrapper => {
    const id = wrapper.id.split('-')[1];
    sliders[id] = {
        currentIndex: 0,
        images: wrapper.querySelectorAll('img')
    };
    updateSlider(id);
});

function changeSlide(id, direction) {
    const slider = sliders[id];
    slider.currentIndex += direction;
    if (slider.currentIndex < 0) slider.currentIndex = slider.images.length - 1;
    if (slider.currentIndex >= slider.images.length) slider.currentIndex = 0;
    updateSlider(id);
}

function updateSlider(id) {
    const wrapper = document.getElementById(`slider-${id}`);
    const slider = sliders[id];
    const offset = -slider.currentIndex * 100;
    wrapper.style.transform = `translateX(${offset}%)`;

    const dotContainer = document.getElementById(`dots-${id}`);
    dotContainer.innerHTML = '';
    slider.images.forEach((_, index) => {
        const dot = document.createElement('span');
        dot.className = 'dot' + (index === slider.currentIndex ? ' active' : '');
        dot.onclick = () => {
            slider.currentIndex = index;
            updateSlider(id);
        };
        dotContainer.appendChild(dot);
    });
}
</script>

</body>
</html>