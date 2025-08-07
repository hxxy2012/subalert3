<?php
// 优化后的管理员端页脚 - layout/admin_footer.php
?>

    </main>

    <!-- 简化的页脚 - 移除大块蓝黑色背景
    <footer class="admin-footer-minimal">
        <div class="container">
            <div class="footer-content-minimal">
                <span>&copy; <?php echo date('Y'); ?> SubAlert</span>
            </div>
        </div>
    </footer>-->

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

    <!-- 新增的CSS样式，用于替代原有的大块蓝黑色页脚 -->
    <style>
    .admin-footer-minimal {
        background: transparent;
        border-top: 1px solid var(--gray-200);
        padding: 1rem 0;
        margin-top: 2rem;
    }

    .footer-content-minimal {
        text-align: center;
        color: var(--gray-500);
        font-size: 0.875rem;
    }

    /* 响应式优化 */
    @media (max-width: 768px) {
        .admin-footer-minimal {
            padding: 0.75rem 0;
            margin-top: 1.5rem;
        }

        .footer-content-minimal {
            font-size: 0.8rem;
        }
    }
    </style>
</body>
</html>