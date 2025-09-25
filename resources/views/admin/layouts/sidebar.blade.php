<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
  <span class="brand-text font-weight-light">Kasir</span>
</a>

    <!-- Sidebar -->
<div class="sidebar d-flex flex-column" style="height: 100%;">
  <!-- Sidebar Menu -->
  <nav class="mt-2 d-flex flex-column flex-grow-1">
    <ul class="nav nav-pills nav-sidebar flex-column flex-grow-1" data-widget="treeview" role="menu" data-accordion="false">
      
      {{-- Menu Atas --}}
      <li class="nav-item">
        <a href="/admin/transaksi" class="nav-link {{ Request::is('admin/transaksi*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-exchange-alt"></i>
          <p>Transaksi</p>
        </a>
      </li>

      <li class="nav-item">
        <a href="/admin/produk" class="nav-link {{ Request::is('admin/produk*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-table"></i>
          <p>Produk</p>
        </a>
      </li>

      <li class="nav-item">
        <a href="/admin/kategori" class="nav-link {{ Request::is('admin/kategori*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-list"></i>
          <p>Kategori</p>
        </a>
      </li>

      <li class="nav-item">
        <a href="/admin/user" class="nav-link {{ Request::is('admin/user*') ? 'active' : '' }}">
          <i class="nav-icon fas fa-users"></i>
          <p>User</p>
        </a>
      </li>

      {{-- Spacer untuk dorong logout ke bawah --}}
      <li class="flex-grow-1"></li>

      {{-- Logout --}}
      <div style="border-top: 1px solid #4f5962;">
        <li class="nav-item">
            <a href="/logout" class="nav-link {{ Request::is('admin/logout*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
            </a>
        </li>
      </div>

    </ul>
  </nav>
</div>
<!-- /.sidebar -->

  </aside>

  <div class="content-wrapper">