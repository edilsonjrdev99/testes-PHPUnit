## Equivalência

Equivalência diz que não precisamos usar todos os valores para testar, por exemplo, se precisamos testar uma feature que a idade é de maior ou igual que 18 e menor que 60, podemos separar os testes em classes e valores

| Classe             | Valores | Teste    |
| valor menor que 18 | 17      | Inválido |
| entre 18 e 60      | 30      | Válido   |
| valor maior que 60 | 61      | Inválido |

## Valor limite ou teste de borda

É quando testamos os valores que estão no limite do teste, por exemplo:

**Regra**: aceita idade igual ou maior que 18 e igual ou menor que 60

| Valores | Teste    |
| 17      | Inválido |
| 18      | Válido   |
| 19      | Válido   |
| 59      | Válido   |
| 60      | Válido   |
| 61      | Inválido |

## Data Providers

O `PHPUnit` permite criarmos uma função responsável por retornar os dados que precisamos na hora de preparar os dados para os testes, para isso precisamos criar uma função no arquivo de teste do tipo `public static` e retornar um array de arrays, onde cada índice do array é um tipo de parâmetro e para informar ao `PHPUnit` que a função representa um `Data Provider` basta usarmos a anotation `#[DataProvider('nomeDoMetodo')]`, veja o exemplo para visualizar melhor:

```php
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AgeTest extends TestCase
{
    #[DataProvider('ageProvider')]
    public function testValidateAge(int $age, bool $expected): void
    {
        $this->assertSame($expected, validateAge($age));
    }

    public static function ageProvider(): array
    {
        return [
            // [$age, $expected]
            [17, false],
            [30, true],
            [61, false],
        ];
    }
}
```

## Métodos setUp e tearDown - Fixtures

`setUp` é um método do PHPUnit responsável por executar um código antes de cada método de teste. **Quando queremos que algo seja instanciado uma única vez para todos os testes, usamos `public static function setUpBeforeClass()`.**

```php
protected function setUp(): void {
  $this->customer = new Customer('João', 25, $address);
}

public function testShouldSumProductsCorrectly(): void {
  $customer = $this->customer;
}
```

O método `tearDown` é o oposto do `setUp`, ele é chamado após cada método de teste. Para que seja executado uma única vez após todos os métodos de teste, usamos `public static function tearDownAfterClass()`.

O ciclo por cada método de teste é:

```
setUpBeforeClass()
    setUp() → testAlgumaCoisa() → tearDown()
    setUp() → testOutraCoisa() → tearDown()
tearDownAfterClass()
```

## Documentação de testes

O PHPUnit oferece mais de uma forma de documentar e reportar os testes executados:

- **`phpunit.xml`** — arquivo de configuração principal do PHPUnit. Define suítes de testes, cobertura de código, filtros e outras opções. É o método mais comum. **Como separar tipos de teste**: podemos definir o path para cada tipo de teste, por exemplo `tests/unit` para unitários. O recomendado é usar apenas uma palavra no `name` do `testsuite`, porque ela será usada no parâmetro `php vendor/bin/phpunit --testsuite="unit"`

```xml
<phpunit bootstrap="vendor/autoload.php" colors="true">
  <testsuites>
    <testsuite name="unit">
      <directory>tests</directory>
    </testsuite>
  </testsuites>

  <logging>
    <junit outputFile="logs/junit.xml"/>
    <testdoxText outputFile="logs/testdox.txt"/>
    <testdoxHtml outputFile="logs/testdox.html"/>
  </logging>
</phpunit>
```

- **`--testdox`** — flag de linha de comando que gera uma saída legível derivada dos nomes dos métodos de teste:
  ```
  Cart
   ✔ Should sum products correctly
   ✔ Must remove the product correctly
  ```

- **`--log-junit arquivo.xml`** — gera um relatório no formato JUnit, amplamente utilizado por ferramentas de CI como GitHub Actions e Jenkins.

- **`--coverage-html pasta/`** — gera um relatório de cobertura de código em HTML (requer Xdebug ou PCOV instalado).

- **`--coverage-text`** — exibe o relatório de cobertura diretamente no terminal.

![Exemplo de configuração do xml do PHPUnit](https://docs.phpunit.de/en/13.2/xml-configuration-file.html#the-testsuite-element)
![Tipos de arquivo de log](https://docs.phpunit.de/en/13.2/xml-configuration-file.html#the-logging-element)

## phpunit.xml e CI/CD

O `phpunit.xml` é commitado no repositório e serve como contrato do projeto: qualquer ambiente que clonar o repositório saberá exatamente como rodar os testes, sem precisar passar flags manualmente.

No CI/CD (GitHub Actions, por exemplo), o fluxo é:

```
1. GitHub clona o repositório (phpunit.xml já está lá)
2. CI roda: php vendor/bin/phpunit
3. PHPUnit lê o phpunit.xml - executa os testes - gera logs/junit.xml
4. CI lê o junit.xml e exibe os resultados na interface
```

O arquivo `logs/` fica no `.gitignore` pois é um artefato gerado — ele existe apenas na máquina do CI durante a execução e é descartado depois.

Exemplo de workflow no GitHub Actions:

```yaml
- name: Run tests
  run: php vendor/bin/phpunit

- name: Publish test results
  uses: mikepenz/action-junit-report@v4
  with:
    report_paths: logs/junit.xml
```

Isso garante que toda vez que um código for enviado ao repositório, os testes rodem automaticamente e o resultado fique visível diretamente na aba **Actions** do GitHub.

## TDD

O `TDD` - Test-Driven Development é o desenvolvimento orientado a testes, esse conceito diz que devemos criar o teste primeiramente e depois desenvolver e é feito em duas etapas:

1. Escreva o teste para uma funcionalidade que não existe, ele deve falhar
2. Desenvolva o código do jeito mais simples possível para que o teste passe
3. Refatore o código sem alterar o comportamento e garanta que o teste continue passando

## Dublê de teste

Utilizado para criar um mock de alguma entidade que estamos testando, para garantir que os testes sejam idempotentes. Se, por exemplo, chamarmos uma entidade que cria registros nos testes, quando esse teste for executado pela segunda vez provavelmente dará erro porque vai duplicar os registros. Nesse caso podemos usar os dublês de teste: classes idênticas às entidades, mas com dados mockados (fictícios).

**Mock manual via herança (injeção de dependência)**

Criamos uma classe que estende a entidade real e sobrescrevemos apenas os métodos que precisamos controlar:

```php
class CartMock extends Cart {
    public function __construct() {
        parent::__construct('cart-mock');
    }

    public function getSubtotalCart(): float {
        return 100.0; // retorna sempre o mesmo valor fixo
    }
}

public function testMethod(): void {
    $cartMock = new CartMock();
    $this->assertEquals(100.0, $cartMock->getSubtotalCart());
}
```

**Método `createMock`**: Ficar criando classe de mock não é eficiente, para isso temos esse método do PHPUnit. Ele gera automaticamente um mock da classe e nos permite "ensinar" como cada método deve se comportar:

```php
public function testMethod(): void {
    $product  = new Product(1, 'Calçado novo', 100);
    $cartMock = $this->createMock(Cart::class);

    $cartMock->method('addProduct')->willReturn([$product]);
}
```

**Métodos de expectativa (`expects`)**

Permitem verificar quantas vezes e com quais parâmetros um método do mock foi chamado durante o teste:

```php
public function testMethod(): void {
    $product  = new Product(1, 'Teclado', 100.0);
    $cartMock = $this->createMock(Cart::class);

    // Verifica que addProduct é chamado exatamente 1 vez com $product como argumento
    $cartMock->expects($this->once())
             ->method('addProduct')
             ->with($product)
             ->willReturnSelf();

    $cartMock->addProduct($product);
}
```

Outros matchers disponíveis:

| Matcher | Comportamento |
|---|---|
| `$this->once()` | Exatamente 1 vez |
| `$this->exactly(n)` | Exatamente n vezes |
| `$this->never()` | Nunca deve ser chamado |
| `$this->atLeastOnce()` | Pelo menos 1 vez |

Para ignorar o construtor completamente (útil quando os argumentos são difíceis de montar):

```php
$cartMock = $this->getMockBuilder(Cart::class)
                 ->disableOriginalConstructor()      // não chama o __construct
                 ->onlyMethods(['getSubtotalCart'])  // só esse método é mockado
                 ->getMock();
```

## Tipos de dublês

1. **Dummy**: é usado apenas para preencher um parâmetro obrigatório
```php
$address = new Address(0, '', '', 0, '');
$customer = new Customer('João', 27, $address);
// $address é um Dummy
```

2. **Stub**: Retorna dados controlados, sem varificação
```php
// Só interessa o valor que vem de volta
$cartMock->method('getSubtotalCart')->willReturn(150.0);
```

3. **Mock**: Tem expectativas
```php
// Se addProduct não for chamado, o teste falha
$cartMock->expects($this->once())->method('addProduct')->with($product);
```

4. **Spy** — como Stub, mas grava o que aconteceu para verificar depois.

5. **Fake** — implementação real simplificada (ex: banco de dados em memória em vez de PostgreSQL).

## testes de integração

Testes de integração são testes responsáveis por verificar o fluxo do código com um fator externo, seja o banco, uma API, ele testa a integração do nosso código com um recurso externo I/O. **exemplo**: Precisamos testar se ocorre a persistência de um determinado dado no banco

1. Em caso de testar banco de dados, é importante atentarmos ao fato de não usar o banco de produção, o ideal seria criar um novo banco para testes, mas se em algum caso for necessário testar o banco de dados de produção, podemos usar a transação (inicia uma transação após a conexão do banco e após o teste damos o rollback).

2. SQLite é uma opção conveniente para testes por ser leve e não precisar de instalação, mas tem um problema: ele se comporta diferente do MySQL e do PostgreSQL (tipos de dados, constraints, sintaxe). Testes que passam no SQLite podem falhar em produção. Para testes de integração mais confiáveis, o ideal é usar o mesmo banco da produção (não a mesma conexão, um banco com a mesma estrutura), mas em uma instância isolada — o Docker é bastante usado para isso.

3. **verificação de estado após operação**: Após persistir um dado, o teste faz um SELECT para confirmar que ele realmente foi salvo. Isso é o padrão recomendado em testes de integração é o "Assert" do ciclo Arrange-Act-Assert. Sem essa verificação, o teste não garante que a persistência realmente aconteceu. O que pode ser polêmico é fazer isso em *testes unitários* (acessar banco real em vez de usar mock), mas em testes de integração essa verificação é esperada e necessária.

4. **teste intermediário**: É uma extensão do padrão AAA onde adicionamos assertions no meio do fluxo, não só no final. Por exemplo: após criar um usuário, verificamos se ele existe antes de continuar o teste; depois testamos o que realmente queríamos. Aumenta a confiabilidade porque, se algo falhar no meio, sabemos exatamente onde parou, mas também aumenta a complexidade do teste.

5. Os testes de integração não são exclusivamente para banco de dados, podemos por exemplo testar APIs, até mesmo as nossas. **Postman**: O Postman possui uma sessão específica para testar endpoints e já possui alguns sneepts para auxiliar os testes

![Exemplo de script de teste automatizado do Postman](https://learning.postman.com/docs/tests-and-scripts/write-scripts/test-examples/?utm_source=chatgpt.com)