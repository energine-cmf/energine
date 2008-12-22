<div class="mainform">
<form action="" method="post" enctype="multipart/form-data">
	<fieldset>
		<legend>Пароль к базе данных для создания дампа</legend>
		<div class="field text">
			<div class="name"><label for="password">MySQL пароль</label>: </div>
			<div class="control">
				<input type="hidden" name="createDump" value="1" />
				<input type="password" class="text" name="password" value="" />
			</div>
		</div>
	</fieldset>
    <div class="field">
        <div class="name">&#160;</div>
        <div class="control"><input type="submit" name="install" value="Создать дамп" class="submit" /></div>
    </div>
</form>
</div>