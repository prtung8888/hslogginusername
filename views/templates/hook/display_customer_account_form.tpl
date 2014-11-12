{**
 * Logging in with username
 *
 * @author    PrestashopExtensions.com
 * @copyright PrestashopExtensions.com
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}

<div class="required form-group">
	<label for="username">{l s='Username'} <sup>*</sup></label>
	<input type="text" class="is_required validate form-control" data-validate="isGenericName" id="username" name="username" value="{if isset($smarty.post.username)}{$smarty.post.username}{/if}" />
</div>