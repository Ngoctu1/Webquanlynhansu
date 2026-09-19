{{-- ================================================================
     FOOTER
     resources/views/admin/partial/footer.blade.php
     ================================================================ --}}
<footer id="footer">
    <div class="footer-inner d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">

        {{-- Copyright --}}
        <div class="footer-copy d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary" style="font-size:1rem;"></i>
            <span>&copy; 2026 <strong>Hệ thống Quản lý Nhân sự</strong>. Bảo lưu mọi quyền.</span>
        </div>

        {{-- Center: Links --}}
        <div class="footer-links d-none d-md-flex align-items-center gap-3">
            <a href="#" class="footer-link">Hỗ trợ</a>
            <span class="footer-dot"></span>
            <a href="#" class="footer-link">Chính sách</a>
            <span class="footer-dot"></span>
            <a href="#" class="footer-link">Liên hệ</a>
        </div>

        {{-- Version Info --}}
        <div class="footer-version d-flex align-items-center gap-2">
            <span class="version-badge">
                <i class="bi bi-lightning-charge-fill"></i> v2.1.0
            </span>
            <span class="footer-separator d-none d-sm-inline">|</span>
            <span class="footer-build">Build: Sep 2026</span>
        </div>

    </div>
</footer>

{{-- ===== Footer Styles ===== --}}
<style>
    #footer {
        background: var(--header-bg, #ffffff);
        border-top: 1px solid var(--border-color, #e2e8f0);
        padding: 14px 28px;
        margin-top: 28px;
    }

    .footer-inner {
        font-size: 0.8rem;
        color: var(--text-secondary, #64748b);
    }

    .footer-copy strong {
        color: var(--text-primary, #1e293b);
        font-weight: 600;
    }

    /* Footer Links */
    .footer-link {
        color: var(--text-secondary, #64748b);
        text-decoration: none;
        font-weight: 500;
        transition: color .2s;
    }

    .footer-link:hover { color: var(--primary-color, #2563eb); }

    .footer-dot {
        width: 4px;
        height: 4px;
        background: #cbd5e1;
        border-radius: 50%;
    }

    /* Version Badge */
    .version-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #dbeafe;
        color: #2563eb;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .footer-separator {
        color: #e2e8f0;
    }

    .footer-build {
        color: #94a3b8;
        font-size: 0.75rem;
    }

    @media (max-width: 575.98px) {
        #footer { padding: 12px 16px; }
        .footer-inner { font-size: 0.75rem; }
    }
</style>
