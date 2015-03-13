<div id='error_warning' style="color:red"></div>
<div><label for='happened_date'>贴息日期:</label><input type='text' id='happened_date' /></div>
<div>银行承兑帐户余额:<span id='remained'><?php echo $this->original['remained'];?>元</span></div>
<div><label for="hb_id">贴息人:</label>
    <select id='hb_id'>
        <?php
            foreach($this->txr as $txr){
                echo '<option value="'.$txr['id'].'">'.$txr['name'].'</option>'; 
            }
        ?>
    </select>
</div>
<div><label for='amount'>贴息金额:</label><input type='text' id='amount' />元</div>
<div><label for="input">转入帐户:</label>
    <select id='input'>
        <?php
            foreach($this->accounts as $account){
                echo '<option value="'.$account['id'].'">'.$account['name'].'('.$account['account_no'].')</option>'; 
            }
        ?>
    </select>
</div>
<div><label for='amount'>贴息费用:</label><input type='text' id='amount' />元（从转入账户中扣除）</div>
<div>
    <label for='jbr_id'>经办人:</label><select id='jbr_id'>
        <?php
            foreach($this->jbr as $jbr_id=>$name){
                echo '<option value="'.$jbr_id.'">'.$name.'</option>'; 
            }
        ?>
    </select>
</div>
