<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
      <a class="navbar-brand" href="#">Navbar</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav ms-auto">
          <a class="nav-link me-3" aria-current="page" href="/">Beranda</a>
          <a class="nav-link me-3" href="#about">Tentang</a>
          <a class="nav-link me-3" href="#statistic">Statistik</a>
          <a class="nav-link" href="{{ route('live-score') }}">Live Score</a>
        </div>
      </div>
    </div>
  </nav>

  @push('scripts')
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const navLinks = document.querySelectorAll(".navbar .nav-link");

      navLinks.forEach(link => {
        link.addEventListener("click", function() {
          navLinks.forEach(l => l.classList.remove("active"));
          this.classList.add("active");
        });
      });
    });
  </script>

  @endpush
