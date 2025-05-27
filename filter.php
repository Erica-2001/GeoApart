<?php include 'navigation.php'; ?>
<?php include 'db_connect.php'; ?>
 <!-- TABS -->
   <br><br><br><br>
  <nav class="tab-nav">
    <button class="tab active" id="feed-tab">Feed</button>
    <button class="tab" id="map-tab">Map View</button>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="container">

    <!-- FILTERS -->
    <section class="filters">
      <h2>Browse Type of Apartment</h2>
      <div class="filter-tags">
        <button>Studio</button>
        <button>Loft</button>
        <button>Duplex</button>
        <button>Micro</button>
      </div>

      <h2>Price Range</h2>
      <div class="price-range">
        <button>Php 1,000 - 2,000</button>
        <button>Php 2,100 - 3,000</button>
        <button>Php 3,100 - 4,000</button>
      </div>
    </section>

  </main>

  <!-- Optional JS file for tab switching or other interactivity -->
  <script src="script.js"></script>
</body>
</html>
<script>
  document.getElementById("map-tab").addEventListener("click", function () {
    window.location.href = "mapview.php";
  });
</script>
<script>
  document.getElementById("feed-tab").addEventListener("click", function () {
    window.location.href = "index.php";
  });
</script> 	