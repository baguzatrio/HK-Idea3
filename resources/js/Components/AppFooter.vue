<template>
  <footer class="site-footer">
    <div class="footer-container">

      <!-- Bisnis Kami -->
      <!-- <div class="footer-col">
        <h3 class="footer-heading">BISNIS KAMI</h3>
        <ul class="footer-links">
          <li><a href="#">Trans Sumatera</a></li>
          <li><a href="#">Operasi dan Pemeliharaan Jalan Tol</a></li>
          <li><a href="#">Jasa Konstruksi dan EPC</a></li>
          <li><a href="#">Manufaktur</a></li>
          <li><a href="#">Pengembangan Properti</a></li>
        </ul>
      </div> -->

      <!-- Quick Link -->
      <!-- <div class="footer-col">
        <h3 class="footer-heading">QUICK LINK</h3>
        <ul class="footer-links">
          <li><a href="#">Pengumuman</a></li>
          <li><a href="#">Pengumuman Pelelangan</a></li>
          <li><a href="#">Report</a></li>
          <li><a href="#">More Publication</a></li>
        </ul>
      </div> -->

      <!-- Tools -->
      <!-- <div class="footer-col">
        <h3 class="footer-heading">TOOLS</h3>
        <ul class="footer-links">
          <li><a href="#">WBS</a></li>
          <li><a href="#">PPID</a></li>
          <li><a href="#">E-Procurement</a></li>
          <li><a href="#">CSIRT</a></li>
        </ul>
      </div> -->

      <!-- Big Title Area -->
      <div class="flex-1 flex items-center lg:pr-10 pb-8 lg:pb-0">
        <a href="https://www.hutamakarya.com/">
          <h1 class="text-6xl md:text-8xl font-black text-white/50 tracking-tight leading-tight uppercase select-none">
            Hutama <br>Karya
          </h1>
        </a>
      </div>

      <!-- Head Office -->
      <div class="footer-col">
        <h3 class="footer-heading">HEAD OFFICE</h3>
        <p class="footer-address">
          HK Tower, Jl Letjen MT Haryono <br>Kav. 8, Cawang,
          East Jakarta City,<br> Jakarta 13340
        </p>

        <h3 class="footer-heading footer-heading--part-of">PART OF</h3>
        <div class="footer-logo">
          <!-- Ganti src dengan path logo Danantara Indonesia kamu -->
          <a href="https://www.danantaraindonesia.co.id/id" target="_blank" rel="noopener noreferrer">
            <img src="/images/logo/danantara-logo.png" alt="Danantara Indonesia" />
          </a>
        </div>
      </div>

    </div>


    <!-- Bottom bar -->
    <div class="footer-bottom">
      <div class="footer-bottom-line"></div>
    </div>

    <p class="text-center text-sm text-gray-400 py-6">
      &copy; {{ currentYear }} PT Hutama Karya (Persero). All rights reserved.
    </p>
  </footer>
</template>

<script setup lang="ts">

const currentYear = new Date().getFullYear();

</script>

<style scoped>
.site-footer {
  background-color: #1e2227;
  color: #a0a8b3;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  padding: 60px 0 0;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
}


/* Heading */
.footer-heading {
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin: 0 0 20px;
}

.footer-heading--part-of {
  margin-top: 28px;
}

/* Links */
.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.footer-links a {
  color: #a0a8b3;
  text-decoration: none;
  font-size: 0.88rem;
  transition: color 0.2s ease;
}

.footer-links a:hover {
  color: #ffffff;
}

/* Address */
.footer-address {
  font-size: 0.88rem;
  line-height: 1.6;
  color: #a0a8b3;
  margin: 0;
}

/* Logo */
.footer-logo img {
  max-width: 180px;
  height: auto;
  margin-top: 8px;
}

/* Fallback logo jika tidak ada gambar */
.footer-logo-placeholder {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 8px;
  background: #2c3038;
  padding: 10px 16px;
  border-radius: 4px;
  width: fit-content;
}

.logo-icon {
  background: #cc0000;
  color: white;
  font-weight: 800;
  font-size: 1.4rem;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
}

.footer-logo-placeholder div {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
  color: #ffffff;
  font-size: 0.95rem;
}

/* Bottom bar */
.footer-bottom {
  margin-top: 48px;
}

.footer-bottom-line {
  height: 1px;
  background: linear-gradient(to right, #cc0000 30%);
}

.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.content {
  flex: 1;
}

/* Responsive */
@media (max-width: 1024px) {
  .footer-container {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .footer-container {
    grid-template-columns: 1fr;
    padding: 0 24px;
  }
}
</style>