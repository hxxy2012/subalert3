<?php
// 用户端页脚 - layout/footer.php
?>
    </main>

    <footer style="background: var(--white); border-top: 1px solid var(--gray-200); padding: 0rem 0; margin-top: 0rem;">
        <div class="container">
            <div class="text-center">
                <span>&copy; <?php echo date('Y'); ?> SubAlert. 专业的订阅管理工具</span>
            </div>
        </div>
    </footer>

    <!-- 回到顶部按钮 -->
    <!-- 
    <button id="backToTop"
            style="display: none; position: fixed; bottom: 2rem; right: 2rem; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; cursor: pointer; z-index: 999; box-shadow: var(--shadow-lg); transition: var(--transition);"
            onclick="scrollToTop()"
            title="回到顶部">
        <i class="fas fa-arrow-up"></i>
    </button>
    -->

    <!--
    <script>
        // 回到顶部功能
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // 添加悬停效果
        document.getElementById('backToTop').addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });

        document.getElementById('backToTop').addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    </script>
    
    -->
</body>
</html>