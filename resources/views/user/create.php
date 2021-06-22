<h3>Create a new user</h3></h3>
<form action="<?=url('/users/create')?>" method="post">
    <label for="first_name">First Name</label><br>
    <input type="text" name="first_name" id="first_name" value="<?=request('first_name')?>">
    <?php if (errors()->has('first_name')):?>
        <span style="color:red"><?=errors()->first('first_name')?></span>
    <?php endif;?>
    <br>

    <label for="last_name">Last Name</label><br>
    <input type="text" name="last_name" id="last_name" value="<?=request('last_name')?>">
    <?php if (errors()->has('last_name')):?>
        <span style="color:red"><?=errors()->first('last_name')?></span>
    <?php endif;?>
    <br>

    <label for="email">Email</label><br>
    <input type="email" name="email" id="email" value="<?=request('email')?>">
    <?php if (errors()->has('email')):?>
        <span style="color:red"><?=errors()->first('email')?></span>
    <?php endif;?>
    <br>

    <input type="hidden" name="_csrf" value="<?=csrf()?>"><br>
    <button type="submit">Create</button>
</form>