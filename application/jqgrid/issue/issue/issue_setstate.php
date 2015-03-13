<label for="state">Plase select owner:</label>
<select id="state">
<?php
    foreach($this->states as $key=>$v){
        print_r('<option value="'.$v['id'].'">'.$v['name'].'</option>');
    }
?>
</select>
