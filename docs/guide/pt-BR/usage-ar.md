# Usando ActiveRecord

Para informações gerais sobre como usar o Yii ActiveRecord, consulte o [guia](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record).

Para definir uma classe Redis ActiveRecord, sua classe de registro precisa se estender de `Yiisoft\Db\Redis\ActiveRecord` e
implemente pelo menos o método `attributes()` para definir os atributos do registro.
Uma chave primária pode ser definida via `Yiisoft\Db\Redis\ActiveRecord::primaryKey()` cujo padrão é `id` se não for especificado.
O PrimaryKey precisa fazer parte dos atributos, então certifique-se de ter um atributo `id` definido se você fizer isso
não especifique sua própria chave primária.

A seguir está um modelo de exemplo chamado `Customer`:

```php
class Customer extends \Yiisoft\Db\Redis\ActiveRecord
{
    /**
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        return ['id', 'name', 'address', 'registration_date'];
    }

    /**
     * @return ActiveQuery defines a relation to the Order record (can be in other database, e.g. elasticsearch or sql)
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    /**
     * Defines a scope that modifies the `$query` to return only active(status = 1) customers
     */
    public static function active($query)
    {
        $query->andWhere(['status' => 1]);
    }
}
```

O uso geral do redis ActiveRecord é muito semelhante ao banco de dados ActiveRecord conforme descrito no
[guia](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record).
Ele suporta a mesma interface e recursos, exceto as seguintes limitações:

- Como redis não suporta SQL, a API de consulta está limitada aos seguintes métodos:
   `where()`, `limit()`, `offset()`, `orderBy()` e `indexBy()`.
- As relações `via` não podem ser definidas através de uma tabela, pois não existem tabelas no redis. Você só pode definir relações através de outros registros.

Também é possível definir relações de ActiveRecords redis para classes [ActiveRecord](https://github.com/yiisoft/active-record) normais e vice-versa.

Exemplo de uso:

```php
$customer = new Customer();
$customer->attributes = ['name' => 'test'];
$customer->save();
echo $customer->id; // id will automatically be incremented if not set explicitly

$customer = Customer::find()->where(['name' => 'test'])->one(); // find by query
$customer = Customer::find()->active()->all(); // find all by query (using the `active` scope)
```
