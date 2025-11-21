@include('layout.head')
  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="/dashboard" class="app-brand-link">
              <span class="app-brand-logo">
                <img src="{{asset('assetsadmin')}}/img/div.png" width="50" height="50" class="cl" alt="">
              </span>
              <span class="app-brand-text demo menu-text fw-bold">TickDesk</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
              <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
            </a>
          </div>

          
          <div class="menu-inner-shadow"></div>

          <!-- / Sidebar Menu -->
          @include('layout.sidebar')

        </aside>

        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          @include('layout.navbar')

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="row">

              <div class=" mb-4">
              <!-- Kontent -->
              @yield('konten')
            

            </div>
            </div>
            </div>
            </div>
            <!-- / Content -->
            <br>

            <!-- Footer -->


            <br>
<br>
<br>

            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl">
                <div
                  class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                  <div>
                    ©
                    <script>
                      document.write(new Date().getFullYear());
                    </script>
                    , Powered By <a href="#" target="_blank" class="fw-semibold">TickDesk</a>
                  </div>
                  <div>
                    {{-- <a onclick="showAlert()" class="footer-link me-4"
                      >License</a
                    > --}}
                    {{-- <a onclick="showAlert()" class="footer-link d-none d-sm-inline-block"
                      >Dukungan</a
                    > --}}
                  </div>
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>

          <!-- Content wrapper -->

        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

<style>

.footer-link{
cursor:pointer;
}

</style>

<script>
function showAlert() {
    Swal.fire({
        title: 'Fitur Segera Hadir!',
        text: '',
        icon: 'error',
        confirmButtonText: 'OK',
        customClass: {
            confirmButton: 'btn btn-primary me-2',
        },
        buttonsStyling: false
    });
}
</script>

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{asset('assetsadmin')}}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{asset('assetsadmin')}}/vendor/libs/popper/popper.js"></script>
    <script src="{{asset('assetsadmin')}}/vendor/js/bootstrap.js"></script>
    <script src="{{asset('assetsadmin')}}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{asset('assetsadmin')}}/vendor/libs/node-waves/node-waves.js"></script>

    <script src="{{asset('assetsadmin')}}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{asset('assetsadmin')}}/vendor/libs/i18n/i18n.js"></script>
    <script src="{{asset('assetsadmin')}}/vendor/libs/typeahead-js/typeahead.js"></script>

    <script src="{{asset('assetsadmin')}}/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <script src="{{asset('assetsadmin')}}/vendor/libs/swiper/swiper.js"></script>
    <script src="{{asset('assetsadmin')}}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

    <!-- Main JS -->
  
    <script src="{{asset('assetsadmin')}}/js/main.js"></script>
    <!-- Page JS -->

  </body>
</html>

@include('layout.session')