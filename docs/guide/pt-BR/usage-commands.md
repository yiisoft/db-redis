# Usando comandos diretamente

Redis tem muitos comandos úteis que podem ser usados diretamente pela conexão. Depois de configurar o aplicativo como
mostrado na seção [Configurando o aplicativo](../../../README.md#Configuring-application), a conexão pode ser obtida da seguinte forma:

```php
$redis = Yii::$app->redis;
```

Depois de feito, pode-se executar comandos. A maneira mais genérica de fazer isso é usando o método `executeCommand`:

```php
$result = $redis->executeCommand('hmset', ['test_collection', 'key1', 'val1', 'key2', 'val2']);
```

Existem atalhos disponíveis para cada comando suportado, portanto, em vez do acima, ele pode ser usado da seguinte maneira:

```php
$result = $redis->hmset('test_collection', 'key1', 'val1', 'key2', 'val2');
```

Para obter uma lista de comandos disponíveis e seus parâmetros, consulte [Comandos redis](https://redis.io/commands).
