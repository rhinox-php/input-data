<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

use Rhino\InputData\InputData;

$post = new InputData($_POST);
$get = new InputData($_GET);
?>
<h1>Post</h1>
<form action="" method="post">
    <div>
        <label>Int</label>
        <input name="i" value="7" />
        <div><?= $post->int('i', null); ?></div>
    </div>
    <div>
        <label>Decimal</label>
        <input name="dec" value="12.9" />
        <div><?= $post->decimal('dec', null); ?></div>
    </div>
    <div>
        <label>String</label>
        <input name="str" value="foo" />
        <div><?= $post->string('str'); ?></div>
    </div>
    <div>
        <label>JSON</label>
        <input name="jsonData" value='{"foo":"bar"}' />
        <?php foreach ($post->json('jsonData') as $key => $value): ?>
            <div><?= $key->string(); ?>: <?= $value->string(); ?></div>
        <?php endforeach; ?>
    </div>
    <div>
        <label>Array</label>
        <input name="items[foo]" value="baz" />
        <input name="items[bar]" value="qux" />
        <?php foreach ($post->arr('items') as $key => $value): ?>
            <div><?= $key->string(); ?>: <?= $value->string(); ?></div>
        <?php endforeach; ?>
    </div>
    <button>Submit</button>
</form>

<h1>Get</h1>
<?php if ($get->isEmpty()): ?>
    <em>(no query params)</em>
<?php endif; ?>
<?php foreach ($get->arr() as $key => $value): ?>
    <div><?= $key->string(); ?>: <?= $value->string(); ?></div>
<?php endforeach; ?>
