</main>
<style>
/* Rodapé compartilhado Daily Hair: estilos isolados para não alterar as páginas. */
.dh-site-footer{position:relative;overflow:hidden;background:linear-gradient(125deg,var(--cream,#f8f1e9) 0%,var(--card,#fffaf6) 58%,var(--cream-dark,#f1e7df) 100%);border-top:1px solid var(--line,#eaded4);color:var(--text,#403331)}
.dh-site-footer::before{content:"";position:absolute;right:-90px;top:-125px;width:330px;height:330px;border:1px solid rgba(198,83,47,.12);border-radius:50%;pointer-events:none}
.dh-site-footer::after{content:"";position:absolute;right:-35px;top:-65px;width:210px;height:210px;border:1px solid rgba(198,83,47,.1);border-radius:50%;pointer-events:none}
.dh-footer-inner{position:relative;z-index:1;max-width:1180px;margin:0 auto;padding:27px 28px 19px}
.dh-footer-top{display:grid;grid-template-columns:minmax(230px,1.4fr) minmax(155px,.8fr) minmax(170px,.9fr);gap:32px;align-items:start}
.dh-footer-logo{display:inline-flex;align-items:center;gap:12px;font-family:'Playfair Display',Georgia,serif;font-weight:600;font-size:23px;color:var(--text,#403331)}
.dh-footer-logo img{width:43px;height:43px;object-fit:contain;border-radius:50%}
.dh-footer-tagline{max-width:325px;margin:7px 0 0;color:var(--muted,#876f65);font-size:12px;line-height:1.55}
.dh-footer-heading{margin:2px 0 10px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1.8px;color:var(--terracotta,#c6532f)}
.dh-footer-nav{display:flex;flex-direction:column;align-items:flex-start;gap:12px}
.dh-site-footer a:not(.dh-footer-logo){font-size:12px;color:var(--text,#403331);transition:color .2s ease,transform .2s ease}
.dh-site-footer a:not(.dh-footer-logo):hover{color:var(--terracotta,#c6532f);transform:translateX(3px)}
.dh-site-footer a:focus-visible{outline:2px solid var(--terracotta,#c6532f);outline-offset:4px;border-radius:3px}
.dh-footer-rule{height:1px;background:linear-gradient(90deg,rgba(198,83,47,.28),rgba(198,83,47,.08));margin:20px 0 13px}
.dh-footer-bottom{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;font-size:12px;color:#8d756a}
.dh-footer-bottom p{margin:0}
.dh-footer-heart{color:var(--terracotta,#c6532f)}
@media(max-width:700px){.dh-footer-inner{padding:27px 24px 20px}.dh-footer-top{grid-template-columns:repeat(2,minmax(0,1fr));gap:19px 24px}.dh-footer-about{grid-column:1/-1}.dh-footer-heading{margin-bottom:12px}.dh-footer-rule{margin:20px 0 13px}}
@media(max-width:390px){.dh-footer-inner{padding-left:18px;padding-right:18px}.dh-footer-top{gap:27px 15px}.dh-footer-logo{font-size:23px}.dh-site-footer a:not(.dh-footer-logo){font-size:13px}}
</style>
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