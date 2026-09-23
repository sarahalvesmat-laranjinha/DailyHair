DAILY HAIR — SITE PHP + MYSQL
 1) Requisitos
XAMPP, WAMP, Laragon ou ambiente equivalente.
PHP 8 ou superior.
MySQL/MariaDB.
Extensão `mysqli` habilitada.
Navegador atualizado.

2) Instalação
1. Extraia a pasta `Daily.Hair.1`.
2. No XAMPP, coloque a pasta dentro de:
   `htdocs`
3. Inicie o **Apache** e o **MySQL** pelo XAMPP.
4. Abra o **phpMyAdmin**.
5. Crie o banco de dados `daily_hair` ou execute o arquivo `database.sql`, que contém a criação do banco e das tabelas.
6. Verifique as configurações de conexão nos arquivos `config.php` e `db.php`.

A configuração presente no projeto utiliza:

* Banco: `daily_hair`
* Host: `localhost`
* Usuário: `root`
* Porta utilizada diretamente em `db.php`: `3307`
* Codificação: `utf8mb4`

>Importante: `config.php` possui a senha `1234`, mas a conexão efetivamente realizada em `db.php` utiliza uma senha vazia (`""`) e a porta `3307`. Caso a configuração do MySQL seja diferente, ajuste o arquivo `db.php`.

3) Acesso

Depois de iniciar o Apache e o MySQL, acesse:
`http://localhost/Daily.Hair.1/`
Também é possível criar uma nova conta pela página de cadastro.
O projeto possui uma conta de demonstração:
E-mail: `demo@dailyhair.com`
Senha: `123456`

4) Funcionalidades

O Daily Hair possui as seguintes funcionalidades:
* Login e logout utilizando sessões.
* Cadastro de usuários.
* Perfil capilar personalizado.
* Edição das informações do cabelo.
* Definição de tipo, curvatura, porosidade e espessura dos fios.
* Registro das necessidades do cabelo.
* Calendário capilar personalizado.
* Definição dos dias de lavagem.
* Rotinas de lavagem personalizadas.
* Sugestão de frequência de lavagem de acordo com o perfil.
* Sequência personalizada de hidratação, nutrição e reconstrução.
* Pesquisa de produtos.
* Filtros e visualização dos produtos.
* Página individual de produto.
* Avaliações com nota e comentário.
* Identificação de avaliações relacionadas ao perfil capilar.
* Cadastro de novos produtos.
* Sistema de aprovação de produtos cadastrados pelos usuários.
Área administrativa para aprovação ou reprovação de produtos.
Upload de imagens de perfil e produtos.
Páginas de privacidade e termos de uso.
Layout responsivo.

## 5) Estrutura principal

text
Daily.Hair.1/
│
├── index.php                 # Página inicial / login
├── register.php              # Cadastro de usuário
├── dashboard.php             # Página inicial após login
├── calendar.php              # Calendário e rotina capilar
├── products.php              # Lista e pesquisa de produtos
├── product.php               # Detalhes e avaliações do produto
├── review.php                # Registro de avaliações
├── perfil.php                # Perfil do usuário
├── meucabelo.php             # Informações do cabelo
├── perfil-publico.php        # Perfil público
├── privacidade.php           # Política de privacidade
├── termos.php                # Termos de uso
├── senha.php                 # Alteração/redefinição de senha
├── logout.php                # Encerramento da sessão
│
├── product-form.php          # Formulário de cadastro de produto
├── product-form-save.php     # Processamento do formulário
├── product-save.php          # Salvamento de produto
├── admin_products.php        # Aprovação de produtos
│
├── config.php                # Configurações do banco
├── db.php                    # Conexão com MySQL
├── auth.php                  # Autenticação e funções auxiliares
├── header.php                # Cabeçalho
├── footer.php                # Rodapé
├── app.js                    # JavaScript
├── style.css                 # Estilos principais
├── product-form.css          # Estilos do cadastro de produtos
│
├── database.sql              # Estrutura e dados iniciais do banco
│
├── images/                   # Imagens dos produtos
├── uploads/
│   └── profiles/             # Imagens de perfil
│
└── outras.png/
    ├── hidrata.png           # Ícone de hidratação
    ├── lavar.png             # Ícone de lavagem
    ├── mascara.png           # Ícone de máscara
    ├── nutri.png             # Ícone de nutrição
    └── reconst.png           # Ícone de reconstrução

6) Banco de dados

O arquivo `database.sql` cria o banco `daily_hair` e contém as principais tabelas:
 `users` — usuários e informações do perfil capilar.
 `products` — produtos cadastrados.
 `reviews` — avaliações dos produtos.

O projeto também possui arquivos SQL adicionais utilizados durante o desenvolvimento e configuração de funcionalidades, incluindo:
`add.sql`
`add.status_createby.sql`
`add.type.sql`
`add.washdays.sql`
`create-is_admin.sql`
`create_aprove_products.sql`
`aprove_products.sql`
`things.washroutines.sql`
`user-addbio.sql`
entre outros.

7) Imagens

As imagens dos produtos estão armazenadas na pasta:
`images/`
O banco de dados utiliza o nome do arquivo da imagem para associá-la ao respectivo produto.
Imagens de perfil ficam em:
`uploads/profiles/`

8) Segurança

As senhas dos usuários são armazenadas utilizando `password_hash()`.
As páginas que exigem autenticação utilizam as funções presentes em `auth.php`, incluindo o controle de sessão por `$_SESSION['user_id']`.
O projeto também utiliza consultas preparadas (`prepare` e `bind_param`) em diversas operações com o banco de dados.

9) Arquivos SQL adicionais

Os arquivos `.sql` presentes na pasta possuem alterações, testes e complementos feitos durante o desenvolvimento do sistema.
Para uma instalação inicial, o arquivo principal é:
`database.sql`
Os demais arquivos devem ser executados somente quando a funcionalidade correspondente precisar ser adicionada ou ajustada.

10) Observação importante

O projeto está configurado para ambiente local e utiliza atualmente a porta `3307` diretamente na conexão existente em `db.php`.
Caso o MySQL esteja utilizando outra porta, usuário, senha ou banco de dados, será necessário alterar as informações de conexão antes de executar o sistema.
O projeto contém arquivos SQL de desenvolvimento, testes e alterações incrementais. Por isso, recomenda-se utilizar o `database.sql` como ponto inicial e aplicar os arquivos adicionais somente quando necessário.
