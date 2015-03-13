<div><label for='happened_date'>转账日期:</label><input type='text' id='happened_date' value='<?php echo $this->currentDate; ?>' /></div>
<fieldset>
    <legend>转出帐户</legend>
转出账户:<?php echo $this->original['name'];?></BR>
转出账号:<?php echo $this->original['account_no'];?></BR>
帐户余额:<span id='remained'><?php echo $this->original['remained'];?></span>元
</fieldset>
</BR>

<fieldset>
    <legend>转入帐户</legend>
    <label for="zjzh_id">转入帐户:</label>
    <select id='zjzh_id'>
        <?php
            foreach($this->accounts as $account){
                echo '<option value="'.$account['id'].'">'.$account['name'].'('.$account['account_no'].')</option>'; 
            }
        ?>
    </select>
    <div id='error_warning' style="color:red"></div>
    <div><label for='total_money'>转账金额:</label><input type='text' id='total_money' />元</div>
    <div><label for='note'>转账原因:</label><textarea id='note' rows=3/></div>
    <div><label for='cost'>转账费用:</label><input type='text' id='cost' value='0'/>元(从转出账户扣除）</div>
</fieldset>       
<div>
    <label for='jbr_id'>经办人:</label><select id='jbr_id'>
        <?php
            foreach($this->jbr as $jbr_id=>$name){
                echo '<option value="'.$jbr_id.'">'.$name.'</option>'; 
            }
        ?>
    </select>

</div>

<script>
    $(function() {
        $( "#happened_date" ).datepicker();
    });
</script>