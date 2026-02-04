<?php
// admin/footer.php
?>
<footer class="admin-footer">
  <div class="footer-inner">
    <span>© <?php echo date('Y'); ?> Talk To Campus — Smart Campus Assistant</span>
    <span class="sep">|</span>
    <span>Developed for Gobi Arts & Science College</span>
  </div>
</footer>

<style>
.admin-footer{
  margin-top:40px;
  background:#ffffff;
  border-top:1px solid #eef2f7;
  padding:14px 20px;
  font-size:14px;
  color:#666;
}
.admin-footer .footer-inner{
  max-width:1200px;
  margin:0 auto;
  display:flex;
  justify-content:center;
  align-items:center;
  gap:8px;
  flex-wrap:wrap;
}
.admin-footer .sep{
  color:#bbb;
}
</style>
