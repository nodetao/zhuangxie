    </div> <!-- 关闭main-content -->

    <script>
    // 侧边栏当前页面高亮
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = location.pathname.split('/').pop();
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            if (link.getAttribute('href').includes(currentPage)) {
                link.classList.add('active');
            }
        });
        
        // 表格行悬停效果
        const tableRows = document.querySelectorAll('table tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseleave', () => {
                row.style.backgroundColor = '';
            });
        });
        
        // 按钮点击效果
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mousedown', () => {
                btn.style.transform = 'translateY(1px)';
            });
            btn.addEventListener('mouseup', () => {
                btn.style.transform = 'translateY(-1px)';
            });
        });
    });
    </script>
</body>
</html>
