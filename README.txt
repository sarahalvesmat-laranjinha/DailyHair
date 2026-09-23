DAILY HAIR — SITE PHP + MYSQL

1) Requisitos
- XAMPP/WAMP/Laragon com PHP 8+ e MySQL/MariaDB.
- Extensão mysqli habilitada.

2) Instalação
- Copie a pasta daily-hair para a pasta htdocs (XAMPP), www (WAMP) ou equivalente.
- Abra phpMyAdmin.
- Execute o arquivo database.sql inteiro.
- O projeto está configurado para MySQL local com:
  host: localhost
  usuário: root
  senha: 1234
  banco: daily_hair
- Se seu MySQL usar outra porta, adapte db.php/config.php.

3) Acesso
- Abra: http://localhost/Daily.Hair.1/
- Você também pode criar uma conta pelo botão “Criar conta”.
- Conta de demonstração: demo@dailyhair.com / 123456

4) O que já funciona
- Login e logout com sessão.
- Cadastro de usuário com perfil capilar.
- Perfil editável.
- Calendário capilar mensal personalizado visualmente pelo perfil.
- Pesquisa e filtros de produtos.
- Página individual de produto.
- Avaliações com nota e comentário.
- Identificação de avaliações de pessoas com tipo de cabelo semelhante.
- Banco MySQL com tabelas users, products e reviews.
- Layout responsivo baseado na tela de login enviada.

5) Estrutura
index.php       login
register.php    cadastro
dashboard.php   página inicial
calendar.php    calendário
products.php    pesquisa de produtos
product.php     detalhes + avaliações
review.php      grava avaliação
profile.php     perfil capilar
logout.php      sair
config.php      credenciais/configuração
db.php          conexão MySQL
auth.php        autenticação/includes
assets/css      estilos
assets/js       JavaScript
database.sql    banco e dados iniciais

6) Observação
As senhas cadastradas pelo site são armazenadas usando password_hash(). A senha 1234 informada para o MySQL fica apenas na configuração de conexão local.

