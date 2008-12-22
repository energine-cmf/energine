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
		<legend>Запрос пароля</legend>
		<div class="field text">
			<div class="name"><label for="password">MySQL пароль</label>: </div>
			<div class="control">
				<input type="password" class="text" name="password" value="" id="password" />
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Восстановить базу данных</label></legend>

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
				<input type="file" class="text" name="dumpfile" id="restoreDBR3" disabled="1" />
			</div>
		</div>
	</fieldset>
    <div class="field">
        <div class="name">&#160;</div>
        <div class="control"><input type="submit" name="install" value="Восстановить" class="submit" /></div>
    </div>
</form>
</div>