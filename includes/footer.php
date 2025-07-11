    </div> <!-- 关闭main-content -->

    <script>
    // 增强型删除确认
    function confirmDelete(item, recordsCount) {
        if (recordsCount > 0) {
            return confirm(`警告：将删除该${item}及其${recordsCount}条关联记录！\n\n此操作不可撤销，确定继续吗？`);
        }
        return confirm(`确定删除该${item}吗？`);
    }
    
    // 侧边栏当前页面高亮
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = location.pathname.split('/').pop();
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            if (link.getAttribute('href').includes(currentPage)) {
                link.classList.add('active');
            }
        });
    });
    </script>
</body>
</html>