<!-- 管理员端页脚 - layout/admin_footer.php -->

    </main>

    <footer style="background: var(--gray-800); color: var(--white); padding: 1.5rem 0; margin-top: 3rem;">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt text-primary"></i>
                        <span>&copy; <?php echo date('Y'); ?> SubAlert 管理后台</span>
                    </div>

                    <!-- 系统状态指示器 -->
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 8px; height: 8px; background: var(--success-color); border-radius: 50%; animation: pulse 2s infinite;"></div>
                        <small style="color: var(--success-color);">系统运行正常</small>
                    </div>
                </div>

                <div class="d-flex gap-4 text-muted">
                    <small>
                        <i class="fas fa-user-shield"></i>
                        管理员：<?php echo htmlspecialchars($_SESSION['admin']['username'] ?? '未知'); ?>
                    </small>
                    <small>
                        <i class="fas fa-clock"></i>
                        <?php echo date('Y-m-d H:i:s'); ?>
                    </small>
                </div>
            </div>

            <!-- 移动端显示 -->
            <div class="text-center mt-3 d-block d-md-none">
                <small style="color: var(--gray-400);">
                    <i class="fas fa-mobile-alt"></i>
                    移动端管理界面
                </small>
            </div>
        </div>
    </footer>

    <style>
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        footer small {
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            footer .d-flex {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            footer .d-flex > div {
                justify-content: center;
            }
        }
    </style>

    <!-- 管理员专用功能脚本 -->
    <script>
        // 自动刷新页面状态（每5分钟）
        let autoRefreshTimer;

        function startAutoRefresh() {
            autoRefreshTimer = setTimeout(function() {
                // 只在仪表盘页面自动刷新
                if (window.location.search.includes('r=dashboard') || !window.location.search.includes('r=')) {
                    window.location.reload();
                }
            }, 300000); // 5分钟
        }

        // 页面可见时启动自动刷新
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearTimeout(autoRefreshTimer);
            } else {
                startAutoRefresh();
            }
        });

        // 页面加载时启动
        startAutoRefresh();

        // 键盘快捷键支持
        document.addEventListener('keydown', function(e) {
            // Ctrl + Shift + D = 快速跳转到仪表盘
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                window.location.href = '/admin.php?r=dashboard';
            }

            // Ctrl + Shift + U = 快速跳转到用户管理
            if (e.ctrlKey && e.shiftKey && e.key === 'U') {
                e.preventDefault();
                window.location.href = '/admin.php?r=users';
            }

            // Ctrl + Shift + S = 快速跳转到设置
            if (e.ctrlKey && e.shiftKey && e.key === 'S') {
                e.preventDefault();
                window.location.href = '/admin.php?r=settings';
            }
        });

        // 显示快捷键提示（按 ? 键）
        document.addEventListener('keydown', function(e) {
            if (e.key === '?' && !e.ctrlKey && !e.altKey) {
                e.preventDefault();
                showShortcutHelp();
            }
        });

        function showShortcutHelp() {
            const helpContent = `
                <h4>管理后台快捷键</h4>
                <ul style="list-style: none; padding: 0; margin-top: 1rem;">
                    <li style="margin-bottom: 0.5rem;"><kbd>Ctrl + Shift + D</kbd> - 跳转到仪表盘</li>
                    <li style="margin-bottom: 0.5rem;"><kbd>Ctrl + Shift + U</kbd> - 跳转到用户管理</li>
                    <li style="margin-bottom: 0.5rem;"><kbd>Ctrl + Shift + S</kbd> - 跳转到系统设置</li>
                    <li><kbd>?</kbd> - 显示此帮助</li>
                </ul>
                <style>
                    kbd {
                        background: var(--gray-100);
                        border: 1px solid var(--gray-300);
                        padding: 0.25rem 0.5rem;
                        border-radius: 4px;
                        font-size: 0.8rem;
                        font-family: monospace;
                    }
                </style>
            `;

            // 简单的弹窗显示
            const existingModal = document.getElementById('shortcutModal');
            if (existingModal) {
                existingModal.remove();
            }

            const modal = document.createElement('div');
            modal.id = 'shortcutModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 2000;
            `;

            modal.innerHTML = `
                <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; width: 90%;">
                    ${helpContent}
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <button onclick="this.closest('#shortcutModal').remove()" class="btn btn-primary">
                            知道了
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // 点击外部关闭
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
    </script>
</body>
</html>