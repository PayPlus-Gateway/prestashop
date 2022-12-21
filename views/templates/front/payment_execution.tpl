

{*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{extends file='page.tpl'}
{block name="page_content_container"}


{*capture name=path*}{*l s='payplus payment' mod='payplus'*}{*/capture*}

<!--<h2>{*l s='Order summary' mod='payplus'*}</h2>-->

{assign var='current_step' value='payment'}

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

	{if isset($display) && $display == 0}
		<div>Please wait ..., you will be automatically redirected.</div>
		<form id="form-payplus" action="{$paymentPageLink}" method="get"></form>
	{else}
		<iframe allowpaymentrequest id="payplusPaymentIframe" src="{$paymentPageLink}" width="100%" height="{$iframeWeight}" frameborder="0"></iframe>
	{/if}
{/if}
{/block}