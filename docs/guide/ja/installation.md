インストール
============

## 必要条件

全てのコンポーネントが正しく動作するためには、最低限、redis バージョン 2.6.12 が必要です。

## Composer パッケージを取得する

このエクステンションをインストールするのに推奨される方法は [composer](https://getcomposer.org/download/) によるものです。

下記のコマンドを実行してください。

```
php composer.phar require yiisoft/db-redis
```

または、あなたの `composer.json` ファイルの `require` セクションに、

```
"yiisoft/db-redis": "~1.0.0"
```

を追加してください。

## アプリケーションを構成する

このエクステンションを使用するためには、アプリケーション構成情報で [[Yiisoft\Db\Redis\Connection|Connection]] クラスを構成する必要があります。

```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'Yiisoft\Db\Redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```

これで、`redis` アプリケーション・コンポーネントによって、redis ストレージに対する基本的なアクセスが提供されるようになります。

```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

利用可能なメソッドの一覧は [[Yiisoft\Db\Redis\Connection]] を参照して下さい。
