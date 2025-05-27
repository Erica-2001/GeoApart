<?php include 'navigation.php'; ?>
 <!-- TABS -->
   <br><br><br>
  

   <style>
       

        /* FILTER SECTION */
        .filter-section {
            margin-top: 10px;
        }

        .filter-label {
            font-weight: bold;
            font-size: 14px;
            margin-top: 8px;
        }

        .filter-options {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 5px;
        }

        .filter-button {
            background: #e0e0e0;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
        }

        

        /* PRICE RANGE */
        .price-range {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .price-input {
            background: white;
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 12px;
            border-radius: 5px;
            width: 80%;
        }

        .price-buttons {
            background: #ddd;
            padding: 5px 8px;
            border-radius: 5px;
            cursor: pointer;
        }

 /* Price Range */
 .price-range {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }

        .price-range button {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 2px solid black;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .price-range span {
            display: flex;
            align-items: center;
            background: #ddd;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: bold;
        }

        /* APARTMENT LIST */
        .apartment-list {
            margin-top: 15px;
        }

        .apartment-card {
            display: flex;
            align-items: center;
            background: #e0e0e0;
            border-radius: 10px;
            padding: 10px;
            margin-top: 8px;
            gap: 10px;
        }

        .apartment-card img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
        }

        .apartment-info {
            font-size: 14px;
            font-weight: bold;
        }

        .apartment-price {
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>


  <!-- MAIN CONTENT -->
  <main class="container">

    <!-- FILTERS -->
     
    <section class="filters">
    <h1>Listing:</h1>
<br>
<p class="filter-label">Offer Type:</p>
            <div class="filter-options">
                <span class="filter-button toggle-active">Rent</span>
            </div>

            <p class="filter-label">Property Type:</p>
            <div class="filter-options">
                <span class="filter-button">Apartment</span>
            </div>

            <p class="filter-label">Subtype:</p>
            <div class="filter-options">
                <span class="filter-button">Bedspace</span>
            </div>

            <p class="filter-label">Allow Short Term Rental?</p>
            <div class="toggle-group">
                <span class="toggle-button toggle-active">Yes</span>
                <span class="toggle-button toggle-active">No</span>
            </div>

            <p class="filter-label">Price Range:</p>
            <div class="price-range">
            <span class="decreasePrice">➖</span>
                <span class="priceDisplay">Php 2,100 - 3,000</span>
                <span class="increasePrice">➕</span>
            </div>
        </section>

    <!-- APARTMENT LIST -->
    <section class="apartment-list">
      <div class="apartment-card">
        <div class="img-placeholder">
          <!-- Replace with real image source -->
          <img src="https://via.placeholder.com/80x80" alt="Apartment" />
        </div>
        <div class="apartment-info">
          <h3>ABC 9-Storey Apartment</h3>
          <p>Php 3,100 - 4,000</p>
        </div>
      </div>

      <div class="apartment-card">
        <div class="img-placeholder">
          <img src="https://via.placeholder.com/80x80" alt="Apartment" />
        </div>
        <div class="apartment-info">
          <h3>AAB 4-Bedroom Apartment</h3>
          <p>Php 1,000 - 2,000</p>
        </div>
      </div>

      <div class="apartment-card">
        <div class="img-placeholder">
          <img src="https://via.placeholder.com/80x80" alt="Apartment" />
        </div>
        <div class="apartment-info">
          <h3>RCG Apartment Complex</h3>
          <p>Php 2,100 - 3,000</p>
        </div>
      </div>
    </section>

  </main>

  <!-- Optional JS file for tab switching or other interactivity -->
  <script src="script.js"></script>
</body>
</html>
