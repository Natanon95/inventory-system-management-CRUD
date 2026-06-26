  </main><!-- /.content -->
</div><!-- /.main-wrap -->
</div><!-- /.layout -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?= $extraScript ?? '' ?>
<script>
  // Show mobile menu toggle on small screens
  if (window.innerWidth <= 768) {
    const t = document.getElementById('sidebar-toggle');
    if (t) t.style.display = 'flex';
  }
</script>
</body>
</html>
