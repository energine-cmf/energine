<div class="mainform">
<form action="" method="post" enctype="multipart/form-data">
    <fieldset>
		<legend>Запрос пароля</legend>
		<div class="field text">
			<div class="name"><label for="password">MySQL пароль</label>: </div>
			<div class="control">
				<input type="password" class="text" name="password" value="" id="password" />
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Создание пользователя</legend>

		<div class="field text">
			<div class="name"><label for="admMail">E-mail администратора</label>: </div>
			<div class="description">Будет использоваться как логин для администраторского входа в систему</div>
			<div class="control">
				<input type="text" class="text" name="admMail" value="demo@energine.org" id="admMail" />
			</div>
		</div>
		<div class="field text">
			<div class="name"><label for="admPassword">Пароль администратора</label>: </div>
			<div class="description">Пароль для администраторского входа в систему</div>
			<div class="control">
				<input type="text" class="text" name="admPassword" value="demo" id="admPassword" />
			</div>
		</div>
	</fieldset>
    <div class="field">
        <div class="name">&#160;</div>
        <div class="control"><input type="submit" name="install" value="Создать" class="submit" /></div>
    </div>
</form>
</div>