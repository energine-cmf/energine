<div class="mainform">
<script type="text/javascript">
<!--
	function switchFields(obj,fields) {
		for (i in fields) {
			document.getElementById(fields[i]).disabled = obj.checked ? false : true;
		}
    }
	function disableFields(fields) {
		for (i in fields) {
			document.getElementById(fields[i]).disabled = true;
		}
    }
	function enableFields(fields) {
		for (i in fields) {
			document.getElementById(fields[i]).disabled = false;
		}
    }
//-->
</script>
<form action="" method="post" enctype="multipart/form-data">
	<fieldset>
		<legend>Основные данные</legend>

		<div class="field text">
			<div class="name"><label for="siteName">Название сайта</label>: </div>
			<div class="description">Внутреннее название сайта (в публичной части нигде не выводится)</div>
			<div class="control">
				<input type="text" class="text" name="siteName" value="" id="siteName" />
			</div>
		</div>
		<div class="field text">
			<div class="name"><label for="siteRoot">Путь от корня сайта</label>: </div>
			<div class="description">Например, http://www.energine.org<b>[/my_site/]</b></div>
			<div class="control">
				<input type="text" class="text" name="siteRoot" value="/" id="siteRoot" />
			</div>
		</div>
		<div class="field text">
			<div class="name"><label for="host">MySQL хост</label>: </div>
			<div class="description">Адрес сервера MySQL</div>
			<div class="control">
				<input type="text" class="text" name="host" value="localhost" id="host" />
			</div>
		</div>
		<div class="field text">
			<div class="name"><label for="DBName">MySQL имя базы данных</label>: </div>
			<div class="description">Имя желаемой базы данных(база данных должна существовать и иметь кодировку UTF8 [utf8_general_ci])</div>
			<div class="control">
				<input type="text" class="text" name="DBName" value="" id="DBName" />

			</div>
		</div>
		<div class="field text">
			<div class="name"><label for="username">MySQL имя пользователя</label>: </div>
			<div class="description">Имя пользователя базы MySQL</div>
			<div class="control">
				<input type="text" class="text" name="username" value="" id="username" />
			</div>
		</div>
		<div class="field text">
			<div class="name"><label for="password">MySQL пароль</label>: </div>
			<div class="description">Пароль базы MySQL</div>
			<div class="control">
				<input type="text" class="text" name="password" value="" id="password" />
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend><input type="checkbox" class="checkbox" name="restoreDB" value="1" id="restoreDB" onclick="switchFields(this,['restoreDBR1','restoreDBR2','restoreDBR3'])" checked /> <label for="restoreDB">Восстановить базу данных?</label></legend>

		<div class="field radiobutton">
			<div class="control">
				<input type="radio" class="radiobutton" name="restoreDBR" id="restoreDBR1" value="1" onclick="disableFields(['restoreDBR3']);" checked="1" />
			</div>
			<div class="name"><label for="restoreDBR1">Восстановить базу из стандартного файла</label></div>
		</div>
		<div class="field radiobutton">
			<div class="control">
				<input type="radio" class="radiobutton" name="restoreDBR" id="restoreDBR2" value="2" onclick="enableFields(['restoreDBR3']);" />
			</div>
			<div class="name"><label for="restoreDBR2">Восстановить базу из вашего файла</label>: </div>
		</div>
		<div class="field text">
			<div class="control">
				<input type="file" class="text" name="dumpfile" id="restoreDBR3" disabled="1" size="56" />
			</div>
		</div>
	</fieldset>
    <div class="field">
        <div class="name">&#160;</div>
        <div class="control"><input type="submit" name="install" value="Установить" class="submit" /></div>
    </div>
</form>
</div>