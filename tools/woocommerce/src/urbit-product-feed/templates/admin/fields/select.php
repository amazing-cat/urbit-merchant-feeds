<select name="<?= $name ?>">
    <?php foreach ($elements as $element): ?>
        <option value="<?= $element['value'] ?>" <?php echo $value==$element['value'] ? 'selected' : '' ?>><?= $element['text'] ?></option>
    <?php endforeach; ?>
</select>