<header id="header"><div class="d-flex align-items-center justify-content-between h-100 px-3 gap-3">
<div class="d-flex align-items-center gap-2">
<button class="btn d-none d-lg-inline-block" onclick="toggleSidebar()" aria-label="Thu gọn menu"><i class="bi bi-layout-sidebar"></i></button>
<button class="btn d-lg-none" onclick="openMobileSidebar()" aria-label="Mở menu"><i class="bi bi-list"></i></button>
<span class="d-none d-md-inline fw-semibold">Hệ thống Quản lý Nhân sự</span></div>
<div class="d-flex align-items-center gap-3"><a href="{{ route('user.dashboard') }}" class="text-decoration-none">{{ auth()->user()->employee?->full_name ?? auth()->user()->email }}</a>
<span class="badge bg-primary">{{ auth()->user()->role->name }}</span>
<form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-outline-secondary btn-sm">Đăng xuất</button></form></div>
</div></header>
