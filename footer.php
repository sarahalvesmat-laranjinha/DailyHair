<?php if (empty($dh_footer_without_main)): ?></main><?php endif; ?>

<footer class="dh-site-footer" aria-label="Rodapé do Daily Hair">
  <div class="dh-footer-inner">
    <div class="dh-footer-top">
      <div class="dh-footer-about">
        <a class="dh-footer-logo" href="dashboard.php" aria-label="Daily Hair — ir para o início">
          <img src="Logo.png" alt="" loading="lazy">
          <span>Daily Hair</span>
        </a>
        <p class="dh-footer-tagline">Seu cabelo, sua rotina. Um espaço para descobrir produtos, compartilhar experiências e cuidar dos seus fios do seu jeito.</p>
      </div>
      <nav aria-label="Navegação do rodapé">
        <h2 class="dh-footer-heading">Explore</h2>
        <div class="dh-footer-nav">
          <a href="dashboard.php">Início</a>
          <a href="calendar.php">Meu calendário</a>
          <a href="products.php">Produtos e avaliações</a>
          <a href="meucabelo.php">Meu cabelo</a>
        </div>
      </nav>
      <nav aria-label="Informações e políticas">
        <h2 class="dh-footer-heading">Informações</h2>
        <div class="dh-footer-nav">
          <a href="termos.php">Termos de uso</a>
          <a href="privacidade.php">Privacidade</a>
          <a href="cookies.php">Política de cookies</a>
        </div>
      </nav>
    </div>
    <div class="dh-footer-rule" aria-hidden="true"></div>
    <div class="dh-footer-bottom">
      <p>© <?= date('Y') ?> Daily Hair. Todos os direitos reservados.</p>
      <p>Feito com <span class="dh-footer-heart" aria-label="carinho">♥</span> para todos os tipos de cabelo.</p>
    </div>
  </div>
</footer>
<script src="assets/js/app.js"></script>
</body>
</html>