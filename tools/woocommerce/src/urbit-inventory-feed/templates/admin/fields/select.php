<select name="<?= $name ?>">
    <?php foreach ($elements as $element): ?>
        <option value="<?= $element['text'] ?>" <?php echo $value==$element['text'] ? 'selected' : '' ?>><?= $element['text'] ?></option>
    <?php endforeach; ?>
</select>