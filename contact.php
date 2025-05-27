<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apartment Listing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 0;
            width: 100%;
        }

        .apartment-card {
            width: 100%;
            max-width: 450px; /* Increased size */
            background: white;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        /* Image Section */
        .image-container {
            width: 100%;
            height: 220px; /* Larger Image */
            border-radius: 10px;
            background: #e0e0e0;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Icons */
        .icons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
        }

        .icons i {
            background: white;
            padding: 8px;
            border-radius: 50%;
            font-size: 16px;
            color: #333;
            cursor: pointer;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .back-icon {
            position: absolute;
            top: 10px;
            left: 10px;
            background: white;
            padding: 8px;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .corner-number {
            position: absolute;
            bottom: 8px;
            left: 8px;
            background: white;
            padding: 4px 8px;
            font-size: 14px;
            border-radius: 5px;
            font-weight: bold;
        }

        /* Apartment Info */
        .apartment-card h3 {
            font-size: 18px;
            margin: 12px 0 6px;
            font-weight: bold;
        }

        .apartment-card p {
            font-size: 16px;
            color: #333;
        }

        /* Details Section */
        .details {
            display: flex;
            justify-content: space-around;
            font-size: 16px;
            margin-top: 10px;
            color: #333;
            font-weight: bold;
        }

        .details i {
            margin-right: 5px;
        }

        /* Location Section */
        .location {
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }

        .location i {
            color: red;
            font-size: 20px;
        }

        /* Features List */
        .features {
            text-align: left;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .features p {
            font-weight: bold;
        }

        .features ul {
            list-style: none;
            padding-left: 0;
        }

        .features li {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 8px 0;
        }

        .features i {
            color: black;
            font-size: 18px;
        }

        /* Contact Button */
        .contact-btn {
            width: 100%;
            background: #ddd;
            color: black;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }

        /* Responsive Fixes */
        @media (max-width: 500px) {
            .apartment-card {
                max-width: 100%;
                border-radius: 0;
            }

            .image-container {
                height: 250px; /* Larger Image for Mobile */
            }

            .details {
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }

            .features {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
  
 <div class="apartment-card">
        <!-- Image Section -->
        <div class="image-container">
            <a href="index.php"><i class="fas fa-arrow-left back-icon"></i></a>
            <img src="https://via.placeholder.com/450x220" alt="Apartment">
            <div class="icons">
                <i class="fas fa-heart"></i>
                <i class="fas fa-share-alt"></i>
            </div>
            <div class="corner-number">5</div>
        </div>

        <!-- Apartment Details -->
        <h3>LLB 3-BEDROOM APARTMENT</h3>
        <p>Php 2,100 - 3,000</p>

        <div class="details">
            <span><i class="fas fa-bed"></i> 3 bedrooms</span>
            <span><i class="fas fa-bath"></i> 2 toilets and baths</span>
        </div>

        <!-- Location -->
        <div class="location">
            <i class="fas fa-map-marker-alt"></i> MENDOZA RD. 1
        </div>

        <!-- Features -->
        <div class="features">
            <p>Features:</p>
            <ul>
                <li><i class="fas fa-check-circle"></i> WiFi router / connection</li>
                <li><i class="fas fa-check-circle"></i> Bed frame</li>
                <li><i class="fas fa-check-circle"></i> Appliances</li>
                <li><i class="fas fa-check-circle"></i> Air conditioning unit</li>
            </ul>
        </div>

        <!-- Contact Section -->
        <p><strong>Interested?</strong></p>
        <button class="contact-btn">Contact Mr. John Doe</button>
    </div>

</body>
</html>
