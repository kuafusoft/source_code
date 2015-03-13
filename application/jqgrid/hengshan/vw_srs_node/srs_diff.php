<label for="tag">Plase select tags:</label>
<?php
	while($row = $this->tag->fetch()){
        print_r('<label><input type="checkbox" name="tag" value="'.$row['id'].'"/>'.$row['name'].'</label>');
    }
?>
</select>
