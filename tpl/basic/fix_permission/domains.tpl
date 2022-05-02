			<div id="setting_domains">
				<div class="divarea_listdomains">
					<label>{$lang.tpl_apac_site}</label>
					<select id="sel_domain">
						{foreach $domains as $val}
							<option {if $val==$select_domain}selected{/if}>{$val}</option>
						{/foreach}
					</select>
					<span class="btn btn-check do_fix" data-domain="{$select_domain}">Виправити права на файли та папки</span>

					<div id="caption_wait_fix" style="display:inline-block;"></div>

				</div>
			</div>

