<label for="user">Plase select owner:</label>
<select id="user">
<?php
    foreach($this->users as $key=>$v){
        print_r('<option value="'.$key.'">'.$v.'</option>');
    }
?>
</select>
