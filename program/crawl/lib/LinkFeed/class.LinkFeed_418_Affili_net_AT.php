<?php
require_once 'text_parse_helper.php';
include_once(dirname(__FILE__) . "/class.LinkFeed_Affili_net.php");
class LinkFeed_418_Affili_net_AT extends LinkFeed_Affili_net
{
	function __construct($aff_id,$oLinkFeed)
	{
		$this->oLinkFeed = $oLinkFeed;
		$this->info = $oLinkFeed->getAffById($aff_id);
		$this->debug = isset($oLinkFeed->debug) ? $oLinkFeed->debug : false;
		
		$this->file = "programlog_{$aff_id}_".date("Ymd_His").".csv";
		$this->soapToken = null;
		$this->soapClient = null;
		$this->soapInboxClient = null;
		$this->DataSource = 184;

        if(SID == 'bdg01'){
            $this->API_USERNAME = NULL;
            $this->API_PASSWORD = NULL;
        }else{
            $this->API_USERNAME = 811103;
            $this->API_PASSWORD = '3vEz0PH8YVvk0qZvAuCi';
            $this->PRODUCT_PASSWORD = 'lkMTn1oejlEDuS8aJmLC';
        }
		
		$this->ctgr_postdata_checked = '&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24radCategoryTree_checked=';
		$this->ctgr_postdata_event = '&__EVENTTARGET=';
		$this->ctgr_postdata_view = '&__VIEWSTATE=';
		$this->ctgr_postdata_view_origin = '%2FwEPDwULLTIwOTQ2NzA3NTEPFgIeE1ZhbGlkYXRlUmVxdWVzdE1vZGUCARYCZg9kFgJmD2QWBAIBD2QWAgICD2QWAmYPFgIeBFRleHQFGmFmZmlsaW5ldCBQdWJsaXNoZXIgUG9ydGFsZAIDDxYCHgVjbGFzcwUbUEFHRV9QUk9HUkFNU19QUk9HUkFNU0VBUkNIFgRmD2QWEAICDxQrAAJkZGQCAw8UKwADZGRkZAIEDxQrAANkZGRkAgwPZBYEZg9kFhACAw8PFgQfAQUSV2VsY29tZSBNb25pY2EgVGFtHgdUb29sVGlwBRJXZWxjb21lIE1vbmljYSBUYW1kZAIFDw8WAh8BBSFMYXN0IGxvZ2luOiZuYnNwOzA0LzA3LzIwMTcgMDU6MzNkZAIHDw8WCB8BBQZMb2dvdXQeCENzc0NsYXNzBQZidXR0b24eC05hdmlnYXRlVXJsBRIvTG9naW4vTG9nb3V0LmFzcHgeBF8hU0ICAmRkAgsPDxYCHwEFYDxpbWcgY2xhc3M9J2xhbmd1YWdlRmxhZ0ltYWdlJyBzcmM9Jy9BcHBfVGhlbWVzL1Bhc3Npb24vSW1hZ2VzL0ZsYWdfMTB4MTdfMi5wbmcnIC8%2BJm5ic3A7RW5nbGlzaGRkAg0PFgIeCWlubmVyaHRtbAWkBjxsaSBvbmNsaWNrPSJqYXZhc2NyaXB0OlBhZ2VIZWFkZXJCYXJfQ2hvb3NlTGFuZ3VhZ2UoJ2RlJykiPjxhIGhyZWY9IiMiPjxzcGFuPiZuYnNwOyZuYnNwOzxpbWcgY2xhc3M9J2xhbmd1YWdlRmxhZ0ltYWdlJyBzcmM9Jy9BcHBfVGhlbWVzL1Bhc3Npb24vSW1hZ2VzL0ZsYWdfMTB4MTdfMS5wbmcnIC8%2BJm5ic3A7R2VybWFuPC9zcGFuPjwvYT48L2xpPjxsaSBvbmNsaWNrPSJqYXZhc2NyaXB0OlBhZ2VIZWFkZXJCYXJfQ2hvb3NlTGFuZ3VhZ2UoJ2ZyJykiPjxhIGhyZWY9IiMiPjxzcGFuPiZuYnNwOyZuYnNwOzxpbWcgY2xhc3M9J2xhbmd1YWdlRmxhZ0ltYWdlJyBzcmM9Jy9BcHBfVGhlbWVzL1Bhc3Npb24vSW1hZ2VzL0ZsYWdfMTB4MTdfMy5wbmcnIC8%2BJm5ic3A7RnJlbmNoPC9zcGFuPjwvYT48L2xpPjxsaSBvbmNsaWNrPSJqYXZhc2NyaXB0OlBhZ2VIZWFkZXJCYXJfQ2hvb3NlTGFuZ3VhZ2UoJ25sJykiPjxhIGhyZWY9IiMiPjxzcGFuPiZuYnNwOyZuYnNwOzxpbWcgY2xhc3M9J2xhbmd1YWdlRmxhZ0ltYWdlJyBzcmM9Jy9BcHBfVGhlbWVzL1Bhc3Npb24vSW1hZ2VzL0ZsYWdfMTB4MTdfNC5wbmcnIC8%2BJm5ic3A7RHV0Y2g8L3NwYW4%2BPC9hPjwvbGk%2BPGxpIG9uY2xpY2s9ImphdmFzY3JpcHQ6UGFnZUhlYWRlckJhcl9DaG9vc2VMYW5ndWFnZSgnZXMnKSI%2BPGEgaHJlZj0iIyI%2BPHNwYW4%2BJm5ic3A7Jm5ic3A7PGltZyBjbGFzcz0nbGFuZ3VhZ2VGbGFnSW1hZ2UnIHNyYz0nL0FwcF9UaGVtZXMvUGFzc2lvbi9JbWFnZXMvRmxhZ18xMHgxN181LnBuZycgLz4mbmJzcDtTcGFuaXNoPC9zcGFuPjwvYT48L2xpPmQCDw8WAh4HVmlzaWJsZWgWAgIBDw8WBB8DBQ1Ub3AgcHVibGlzaGVyHghJbWFnZVVybAUsL0FwcF9UaGVtZXMvUGFzc2lvbi9JbWFnZXMvdG9wX3B1Ymxpc2hlci5wbmcWBh4Hb25jbGljawUiamF2YXNjcmlwdDpPcGVuVG9wUHVibGlzaGVyUG9wdXAoKR4FdGl0bGUFDVRvcCBwdWJsaXNoZXIeBXN0eWxlBQ9jdXJzb3I6cG9pbnRlcjtkAhEPZBYGAgEPDxYCHwEFHjxiPjgxMTEwMzogYnJhbmRyZXdhcmQuY29tPC9iPmRkAgMPDxYCHwEFDlNlbGVjdCBhY2NvdW50ZGQCBQ88KwAJAgAPFgYeDU5ldmVyRXhwYW5kZWRkHgxTZWxlY3RlZE5vZGUFK3VjUGFnZUhlYWRlckJhcl9jdGxBY2NvdW50TGlzdF90dkFjY291bnRzbjEeCUxhc3RJbmRleAICZAgUKwACBQMwOjAUKwACFggfAQVIPGltZyBzcmM9Ii4uL2ltYWdlcy9GbGFncy9GbGFnXzEweDE3XzcuZ2lmIiAvPiZuYnNwO0FjY291bnQgZ3JvdXAgODExMTAzHgVWYWx1ZQUNbWFzdGVyXzgxMTEwMx8FBRNqYXZhc2NyaXB0OnZvaWQoMCk7HghFeHBhbmRlZGcUKwACBQMwOjAUKwACFgofAQUXODExMTAzOiBicmFuZHJld2FyZC5jb20fEAUQcHVibGlzaGVyXzgxMTEwMx4IU2VsZWN0ZWRnHwUFE2phdmFzY3JpcHQ6dm9pZCgwKTsfEWdkZAITDw8WAh8BBQ9NYW5hZ2UgYWNjb3VudHNkZAIBDxYCHwhoFgQCAQ8QDxYCHgdDaGVja2VkaGRkZGQCBQ8PFgIfAQUOQWRtaW4gU2Vzc2lvbiFkZAINDw8WAh8FBRR%2BL1N0YXJ0L0RlZmF1bHQuYXNweGQWAmYPDxYCHg1BbHRlcm5hdGVUZXh0BRlvdXIgbWlzc2lvbjogeW91ciBzdWNjZXNzZGQCEA9kFgQCAw8PFgIfCGhkZAIFDw8WAh8IaGRkAhEPZBYGZg8PFgQeD09uQ2xpZW50U2hvd2luZwUPTXlDbGllbnRTaG93aW5nHg5PbkNsaWVudEhpZGluZwUOTXlDbGllbnRIaWRpbmdkZAIBDw8WBB4RY2VfT25SZXF1ZXN0U3RhcnQFDHJlcXVlc3RTdGFydB4QY2VfT25SZXNwb25zZUVuZAULcmVzcG9uc2VFbmRkZAIDD2QWHgIDD2QWBGYPZBYCZg9kFgJmD2QWAgIBDw8WAh8BBQ5Qcm9ncmFtIHNlYXJjaGRkAgIPZBYEAgEPDxYCHwEF%2BAJUaGlzIGZlYXR1cmUgZW5hYmxlcyB5b3UgdG8gc2VhcmNoIGZvciBwcm9ncmFtcyBhbmQgY2FtcGFpZ25zIHdoaWNoIG1hdGNoIHlvdXIgd2Vic2l0ZS4gWW91IGNhbiBlaXRoZXIgYXBwbHkgdG8gdGhvc2UgcHJvZ3JhbXMgaW1tZWRpYXRlbHkgb3IgdHJhY2sgdGhlIHN0YXR1cyBvZiB5b3VyIHBhcnRuZXJzaGlwcy4gVGhlIG9wdGlvbmFsIGNyaXRlcmlhIGluIHRoZSBwcm9ncmFtIGZpbHRlciBlbmFibGVzIHlvdSB0byBzZWFyY2ggZm9yIHByb2dyYW1zIGFjY29yZGluZyB0byB5b3VyIG5lZWRzLiBJZiB5b3Ugd2FudCB0byBydW4gYSBwYXJ0aWN1bGFyIHNlYXJjaCBvbiBhIHJlZ3VsYXIgYmFzaXMsIHlvdSBjYW4gc2F2ZSB0aGUgZmlsdGVyIG9wdGlvbnMuZGQCAw8PFgIfAQVwVGlwOiBJZiB5b3UgYXJlIGxvb2tpbmcgZm9yIGNyZWF0aXZlcywgZmVlbCBmcmVlIHRvIHVzZSB0aGUgbmV3IGNyZWF0aXZlIHNlYXJjaC4gWW91IGNhbiBoaWRlIHRoZSAiT3VyIHRpcCIgYm94LmRkAgUPFCsAA2RkZGQCBw8UKwADZGRkZAILDxYCHwhoFgICAQ8PFgIfAWVkZAINDw8WAh8IaGRkAg8PZBYQZg8PFgIfAQUVU2F2ZWQgZmlsdGVyIHNldHRpbmdzZGQCAQ8QDxYGHg1EYXRhVGV4dEZpZWxkBQROYW1lHg5EYXRhVmFsdWVGaWVsZAUCSWQeC18hRGF0YUJvdW5kZ2QQFQEQLSBubyBzZWxlY3Rpb24gLRUBAi0xFCsDAWcWAWZkAgIPFggeB29uQ2xpY2sFqAF0b2dnbGVGaWx0ZXIoJ0NvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnRfRnJhbWUxQ29udGVudF91Y0ZpbHRlckNvbnRyb2xfZGl2RGVsZXRlRmlsdGVyJywnQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X3VjRmlsdGVyQ29udHJvbF9kaXZTYXZlRmlsdGVyJywnJywnJywnJykeBXZhbHVlBQ1EZWxldGUgZmlsdGVyHwsFDURlbGV0ZSBmaWx0ZXIfCGhkAgMPFgYfHAWBA3RvZ2dsZUZpbHRlcignQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X3VjRmlsdGVyQ29udHJvbF9kaXZTYXZlRmlsdGVyJywnQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X3VjRmlsdGVyQ29udHJvbF9kaXZEZWxldGVGaWx0ZXInLCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfdWNGaWx0ZXJDb250cm9sX2NiU2F2ZVBlcmlvZCcsJ0NvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnRfRnJhbWUxQ29udGVudF91Y0ZpbHRlckNvbnRyb2xfZGRsQ3VzdG9tRmlsdGVyJywnQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X3VjRmlsdGVyQ29udHJvbF9TYXZlZFBlcmlvZEhpZGRlbkZpZWxkJykfHQUUU2F2ZSBmaWx0ZXIgc2V0dGluZ3MfCwUUU2F2ZSBmaWx0ZXIgc2V0dGluZ3NkAgQPDxYEHwEFDVJlbG9hZCBmaWx0ZXIfCGgWAh8LBQ1SZWxvYWQgZmlsdGVyZAIFDw8WAh8BBRBObyBmaWx0ZXIgY2hvc2VuZGQCBw8WAh8MBQ1kaXNwbGF5Om5vbmU7FgwCAQ8PFgIfAQULU2F2ZSBmaWx0ZXJkZAIDD2QWCAIBDw8WAh8BBQtFbnRlciBuYW1lOmRkAgUPDxYCHwEFFW1heGltdW0gMjUgY2hhcmFjdGVyc2RkAgcPDxYCHgxFcnJvck1lc3NhZ2UFE1BsZWFzZSBlbnRlciBhIG5hbWVkZAIJDw8WAh8eBUdJbnZhbGlkIGZpbHRlciBuYW1lIC0gbXVzdCBub3QgY29udGFpbiBhbnkgb2YgdGhlc2UgY2hhcmFjdGVyczogPCA%2BICcgP2RkAgUPDxYEHwEF3gFTYXZlIHlvdXIgZmlsdGVyIHNldHRpbmdzIGhlcmUuIFJlbW92ZSB0aGUgY2hlY2sgZnJvbSB0aGUgYm94ICJTYXZlIGVuZCBkYXRlIiwgaWYgeW91IGRvIG5vdCB3YW50IHRvIHNhdmUgdGhlIGVuZCBkYXRlLiBPdGhlcndpc2UsIHRoZSBlbmQgZGF0ZSB3aWxsIGJlIHNhdmVkLiBJZiB5b3UgZG8gbm90IHNhdmUgdGhlIGVuZCBkYXRlLCB0aGUgY3VycmVudCBkYXRlIHdpbGwgYmUgdXNlZC4fCGhkZAIHDxAPFgQfAQUNU2F2ZSBlbmQgZGF0ZR8IaGRkZGQCCQ8PFgIfAQUEU2F2ZRYCHwsFBFNhdmVkAgsPFgYfHQUGQ2FuY2VsHxwF6wFjbG9zZUZpbHRlcignQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X3VjRmlsdGVyQ29udHJvbF9kaXZTYXZlRmlsdGVyJywnQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X3VjRmlsdGVyQ29udHJvbF9kaXZEZWxldGVGaWx0ZXInLCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfdWNGaWx0ZXJDb250cm9sX1ZhbGlkYXRpb25TdW1tYXJ5MScpHwsFBkNhbmNlbGQCCA8WAh8MBQ1kaXNwbGF5Om5vbmU7FgYCAQ8PFgIfAQUsQXJlIHlvdSBzdXJlIHlvdSB3YW50IHRvIGRlbGV0ZSB0aGlzIGZpbHRlcj9kZAIDDw8WAh8BBQZEZWxldGUWAh8LBQZEZWxldGVkAgUPFgYfHQUGQ2FuY2VsHxwF6wFjbG9zZUZpbHRlcignQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X3VjRmlsdGVyQ29udHJvbF9kaXZEZWxldGVGaWx0ZXInLCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfdWNGaWx0ZXJDb250cm9sX2RpdlNhdmVGaWx0ZXInLCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfdWNGaWx0ZXJDb250cm9sX1ZhbGlkYXRpb25TdW1tYXJ5MScpHwsFBkNhbmNlbGQCEQ9kFhwCAQ8PFgIfAQUOUHJvZ3JhbSBzZWFyY2hkZAIDDw8WAh8BBQtTZWFyY2ggdGVybWRkAgcPDxYCHx4FIlBsZWFzZSBlbnRlciBhIHZhbGlkIHNlYXJjaCBxdWVyeS5kZAIJDxAPZBYEHwwFDWRpc3BsYXk6bm9uZTseCG9uY2hhbmdlBRxkZGxTZWFyY2hUeXBlX09uQ2hhbmdlKHRoaXMpDxYBZhYBEAUSc2VhcmNoIGluIHByb2dyYW1zBQhwcm9ncmFtc2dkZAILDw8WAh8BBVIoU2VhcmNoIHdpbGwgYmUgcGVyZm9ybWVkIHdpdGggcHJvZ3JhbSB0aXRsZSwgZGVzY3JpcHRpb24sIHByb2dyYW0gSUQgb3Iga2V5d29yZHMpZGQCDQ8PFgIfAQUSUGFydG5lcnNoaXAgc3RhdHVzZGQCDw8QZA8WBmYCAQICAgMCBAIFFgYQBQxBbGwgcHJvZ3JhbXMFDm5vUmVzdHJpY3Rpb25zZxAFHkFjdGl2ZSAmIGFjY2VwdGVkIHBhcnRuZXJzaGlwcwUGYWN0aXZlZxAFBlBhdXNlZAUGcGF1c2VkZxAFFUF3YWl0aW5nIGNvbmZpcm1hdGlvbgUHd2FpdGluZ2cQBR9EZWNsaW5lZCAmIGRlbGV0ZWQgcGFydG5lcnNoaXBzBQdyZWZ1c2VkZxAFDk5vIHBhcnRuZXJzaGlwBQ1ub1BhcnRuZXJzaGlwZ2RkAhEPZBYEAgEPDxYCHwEFB0NvdW50cnlkZAIDDxBkDxYDZgIBAgIWAxAFA0FsbAUBMGcQBQtOZXRoZXJsYW5kcwUDMTIzZxAFB0JlbGdpdW0FAjE3ZxYBZmQCEw8QDxYCHwEFDlByb2dyYW0gZmlsdGVyFgIfCgWhAWphdmFzY3JpcHQ6VG9nZ2xlRmlsdGVyU2V0dGluZ3NWaXNpYmlsaXR5KCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfY2JGaWx0ZXJTZXR0aW5ncycsJ0NvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnRfRnJhbWUxQ29udGVudF90ckZpbHRlclNldHRpbmdzMScpZGRkAhUPFgIfDAUNZGlzcGxheT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%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UgQ29tcGFyaXNvbh8QBQQxNTM0HxNnZGQCEQ8PFgYfAQUVRW50ZXJ0YWlubWVudCAmIE11c2ljHxAFBDE1MTUfE2dkFgJmDw8WBh8BBQdUaWNrZXRzHxAFBDE1MDQfE2dkZAIHDxAPFgIfAQUVQ2hvb3NlIGFsbCBjYXRlZ29yaWVzFgIfCgUiY2JDaGVja0FsbENhdGVnb3JpZXNfT25DbGljayh0aGlzKWRkZAIBD2QWGgIBDw8WAh8BBRZQcm9ncmFtIGNoYXJhY3RlcmlzdGljZGQCAw8QDxYCHwEFEUV4Y2x1c2l2ZSBwcm9ncmFtZGRkZAIFDxAPFgIfAQURVG9wIG1vYmlsZSBwcm9nYW1kZGRkAgcPDxYCHwhoZBYCAgEPEA8WBB8BBRdQYXJ0bmVyc2hpcCBhdXRvLWFjY2VwdB8TaGRkZGQCCQ8QDxYCHwEFIlZvdWNoZXIgY29kZXMvcHJvbW90aW9ucyBhdmFpbGFibGVkZGRkAgsPEA8WAh8BBQ5DUE0gb24gcmVxdWVzdGRkZGQCDQ8QDxYCHwEFFlByb2R1Y3QgZGF0YSBhdmFpbGFibGUWAh8KBRdjYlByb2R1Y3REYXRhX09uQ2xpY2soKWRkZAIPDw8WAh8BBR1Qcm9kdWN0IGRhdGEgdXBkYXRlIGZyZXF1ZW5jeWRkAhEPDxYCHwEFFlVwZGF0ZWQgYXQgbGVhc3QgZXZlcnlkZAITDw8WCB8EBSJyaWdodCBzbWFsbFRleHRib3ggZGlzYWJsZWRUZXh0Ym94HwFlHgdFbmFibGVkaB8GAgIWBh4Hb25mb2N1cwUwcmV0dXJuIENvbnRyb2xzTGlicmFyeS5OdW1iZXJGaWVsZF9PbkZvY3VzKHRoaXMpHglvbmtleWRvd24FOnJldHVybiBDb250cm9sc0xpYnJhcnkuTnVtYmVyRmllbGRfT25LZXlEb3duKHRoaXMsMCxldmVudCkeBm9uYmx1cgU5cmV0dXJuIENvbnRyb2xzTGlicmFyeS5OdW1iZXJGaWVsZF9PbkJsdXIodGhpcyxmYWxzZSwnMCcpZAIVDw8WAh8BBQRkYXlzZGQCFw9kFggCAQ8PFgIfAQURTW9iaWxlIHByb3BlcnRpZXNkZAIDDxAPFgIfAQUOTW9iaWxlIHdlYnNpdGVkZGRkAgUPEA8WAh8BBQpNb2JpbGUgYXBwZGRkZAIHDxAPFgIfAQUQTW9iaWxlIGNyZWF0aXZlc2RkZGQCGQ8WAh8IaBYIAgEPDxYCHwEFCUNvdW50cmllc2RkAgMPEA8WBB8BBQdHZXJtYW55HxNnZGRkZAIFDxAPFgQfAQUHQXVzdHJpYR8TZ2RkZGQCBw8QDxYEHwEFC1N3aXR6ZXJsYW5kHxNnZGRkZAICD2QWHgIBD2QWBAIBDw8WAh8BBQxQYXltZW50IFR5cGVkZAIDDxBkDxYEZgIBAgICAxYEEAUPbm8gcmVzdHJpY3Rpb25zBQ5ub1Jlc3RyaWN0aW9uc2cQBQtQYXlQZXJDbGljawUDUFBDZxAFClBheVBlckxlYWQFA1BQTGcQBQpQYXlQZXJTYWxlBQNQUFNnZGQCAw8PFgIfAQUIVHJhY2tpbmdkZAIFDxAPZBYCHx8FmAFkZGxUcmFja2luZ1R5cGVfT25DaGFuZ2UoJ0NvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnRfRnJhbWUxQ29udGVudF9kZGxUcmFja2luZ1R5cGUnLCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfdGJDb29raWVMaWZlVGltZUxvd2VyTGltaXQnKQ8WBGYCAQICAgMWBBAFD25vIHJlc3RyaWN0aW9ucwUObm9SZXN0cmljdGlvbnNnEAUHU2Vzc2lvbgUHc2Vzc2lvbmcQBQZDb29raWUFBmNvb2tpZWcQBQ5TZXNzaW9uIGNvb2tpZQUNc2Vzc2lvbkNvb2tpZWdkZAIHDw8WAh8BBRhNaW5pbXVtIGNvb2tpZSBsaWZldGltZTpkZAIJDw8WCB8EBSJyaWdodCBzbWFsbFRleHRib3ggZGlzYWJsZWRUZXh0Ym94HwFlHyBoHwYCAhYGHyEFMHJldHVybiBDb250cm9sc0xpYnJhcnkuTnVtYmVyRmllbGRfT25Gb2N1cyh0aGlzKR8iBTpyZXR1cm4gQ29udHJvbHNMaWJyYXJ5Lk51bWJlckZpZWxkX09uS2V5RG93bih0aGlzLDAsZXZlbnQpHyMFOXJldHVybiBDb250cm9sc0xpYnJhcnkuTnVtYmVyRmllbGRfT25CbHVyKHRoaXMsZmFsc2UsJzAnKWQCCw8PFgIfAQUEZGF5c2RkAg0PDxYCHx4FF1BsZWFzZSBlbnRlciBhbiBudW1iZXIuZGQCDw9kFgQCAQ8PFgIfAQUKU0VNLVBvbGljeWRkAgMPEGQPFgVmAgECAgIDAgQWBRAFDEFsbCBwcm9ncmFtcwUObm9SZXN0cmljdGlvbnNnEAUHQWxsb3dlZAUVdW5yZXN0cmljdGVkbHlBbGxvd2VkZxAFG0FsbG93ZWQsIHJlc3RyaWN0aW9ucyBhcHBseQUXYWxsb3dlZFdpdGhSZXN0cmljdGlvbnNnEAUjQWxsb3dlZCAmIGFsbG93ZWQgd2l0aCByZXN0cmljdGlvbnMFI3VucmVzdHJpY3RlZGx5T3JSZXN0cmljdGVkbHlBbGxvd2VkZxAFC05vdCBhbGxvd2VkBQ9ub3RBbGxvd2VkQXRBbGxnZGQCEQ8PFgIfAQUJQ2FtcGFpZ25zZGQCEw8QZA8WA2YCAQICFgMQBQxBbGwgcHJvZ3JhbXMFDm5vUmVzdHJpY3Rpb25zZxAFDk9ubHkgY2FtcGFpZ25zBQxPbmx5Q2FtcGFpZ25nEAUNT25seSBwcm9ncmFtcwUKTm9DYW1wYWlnbmdkZAIVDw8WAh8BBRBQcm9ncmFtIGxpZmV0aW1lZGQCFw8PFgIfAQUIQXQgbGVhc3RkZAIZDw9kFgYfIQUwcmV0dXJuIENvbnRyb2xzTGlicmFyeS5OdW1iZXJGaWVsZF9PbkZvY3VzKHRoaXMpHyIFOnJldHVybiBDb250cm9sc0xpYnJhcnkuTnVtYmVyRmllbGRfT25LZXlEb3duKHRoaXMsMCxldmVudCkfIwU4cmV0dXJuIENvbnRyb2xzTGlicmFyeS5OdW1iZXJGaWVsZF9PbkJsdXIodGhpcyx0cnVlLCcwJylkAhsPDxYCHwEFBm1vbnRoc2RkAh0PDxYCHx4FF1BsZWFzZSBlbnRlciBhbiBudW1iZXIuZGQCFw9kFgJmD2QWAgIBDxAPFgIfAQUKS1BJIGZpbHRlchYCHwoFpgFqYXZhc2NyaXB0OlRvZ2dsZUZpbHRlclNldHRpbmdzVmlzaWJpbGl0eSgnQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X2NiS1BJRmlsdGVyU2V0dGluZ3MnLCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfdHJLUElGaWx0ZXJTZXR0aW5ncycpZGRkAhkPFgIfDAUNZGlzcGxheTpub25lOxYGZg9kFgQCAQ9kFhJmDxYCHwsFUlRoZSBhdmVyYWdlIEVQQyAoZWFybmluZ3MgcGVyIGNsaWNrKSBmb3IgdGhpcyBhZHZlcnRpc2VyIHdpdGhpbiB0aGUgbGFzdCA2IG1vbnRocy4WBmYPZBYCZg9kFgQCAQ8QD2QWAh8KBWVTbGlkZXJDb250cm9sLmNiQWN0aXZhdGVkX09uQ2xpY2sodGhpcywnQ29udGVudFBsYWNlSG9sZGVyQ29udGVudF9GcmFtZTFDb250ZW50X3VjS1BJZUNQQ19kaXZTbGlkZXInKWRkZAIDDxYCHwhnFgICAQ8PFgIfAQUGw5ggRVBDZGQCAQ9kFgRmD2QWAgIBDw8WAh8BBQ0wLjAw4oKsJm5ic3A7ZGQCAg9kFgICAQ8PFgIfAQUbJm5ic3A7JiM4ODA1OyZuYnNwOzEwLjAw4oKsZGQCAg9kFgJmD2QWAgIBDw8WAh8BBR4wLjAw4oKsIC0mbmJzcDsmIzg4MDU7MTAuMDDigqxkZAICDw9kFgIfDAUNZGlzcGxheTpub25lO2QCAw8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAgQPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIFDw9kFgIfDAUNZGlzcGxheTpub25lO2QCBg8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAgcPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIIDw9kFgIfDAUNZGlzcGxheTpub25lO2QCCQ8QD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZGRkAgMPZBYSZg8WAh8LBU9TaG93cyB0aGUgYXZlcmFnZSBjYW5jZWxsYXRpb24gcmF0ZSBvZiB0aGUgYWR2ZXJ0aXNlciB3aXRoaW4gdGhlIGxhc3QgNiBtb250aHMuFgZmD2QWAmYPZBYEAgEPEA9kFgIfCgVwU2xpZGVyQ29udHJvbC5jYkFjdGl2YXRlZF9PbkNsaWNrKHRoaXMsJ0NvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnRfRnJhbWUxQ29udGVudF91Y0tQSUNhbmNlbGF0aW9uUmF0ZV9kaXZTbGlkZXInKWRkZAIDDxYCHwhnFgICAQ8PFgIfAQUUw5ggQ2FuY2VsbGF0aW9uIHJhdGVkZAIBD2QWBGYPZBYCAgEPDxYCHwEFCDAlJm5ic3A7ZGQCAg9kFgICAQ8PFgIfAQUJJm5ic3A7ODAlZGQCAg9kFgJmD2QWAgIBDw8WAh8BBQgwJSAtIDgwJWRkAgIPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIDDw9kFgIfDAUNZGlzcGxheTpub25lO2QCBA8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAgUPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIGDw9kFgIfDAUNZGlzcGxheTpub25lO2QCBw8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAggPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIJDxAPZBYCHwwFDWRpc3BsYXk6bm9uZTtkZGQCAQ9kFgQCAQ9kFhJmDxYCHwsFMFRoZSBhdmVyYWdlIGNvbnZlcnNpb24gcmF0ZSBmb3IgdGhpcyBhZHZlcnRpc2VyLhYGZg9kFgJmD2QWBAIBDxAPZBYCHwoFb1NsaWRlckNvbnRyb2wuY2JBY3RpdmF0ZWRfT25DbGljayh0aGlzLCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfdWNLUElDb252ZXJzaW9uUmF0ZV9kaXZTbGlkZXInKWRkZAIDDxYCHwhnFgICAQ8PFgIfAQUSw5ggQ29udmVyc2lvbiBSYXRlZGQCAQ9kFgRmD2QWAgIBDw8WAh8BBQgwJSZuYnNwO2RkAgIPZBYCAgEPDxYCHwEFCSZuYnNwOzM1JWRkAgIPZBYCZg9kFgICAQ8PFgIfAQUIMCUgLSAzNSVkZAICDw9kFgIfDAUNZGlzcGxheTpub25lO2QCAw8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAgQPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIFDw9kFgIfDAUNZGlzcGxheTpub25lO2QCBg8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAgcPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIIDw9kFgIfDAUNZGlzcGxheTpub25lO2QCCQ8QD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZGRkAgMPZBYSZg8WAh8LBWhTaG93cyB0aGUgYXZlcmFnZSB2YWxpZGF0aW9uIHRpbWUgKGNvbmZpcm1hdGlvbiBvciBjYW5jZWxsYXRpb24pIG9mIG9wZW4gdHJhbnNhY3Rpb25zIG9mIHRoZSBhZHZlcnRpc2VyLhYGZg9kFgJmD2QWBAIBDxAPZBYCHwoFb1NsaWRlckNvbnRyb2wuY2JBY3RpdmF0ZWRfT25DbGljayh0aGlzLCdDb250ZW50UGxhY2VIb2xkZXJDb250ZW50X0ZyYW1lMUNvbnRlbnRfdWNLUElWYWxpZGF0aW9uVGltZV9kaXZTbGlkZXInKWRkZAIDDxYCHwhnFgICAQ8PFgIfAQUSw5ggVmFsaWRhdGlvbiB0aW1lZGQCAQ9kFgRmD2QWAgIBDw8WAh8BBQswZGF5cyZuYnNwO2RkAgIPZBYCAgEPDxYCHwEFGSZuYnNwOyYjODgwNTsmbmJzcDs5MGRheXNkZAICD2QWAmYPZBYCAgEPDxYCHwEFGjBkYXlzIC0mbmJzcDsmIzg4MDU7OTBkYXlzZGQCAg8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAgMPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIEDw9kFgIfDAUNZGlzcGxheTpub25lO2QCBQ8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAgYPD2QWAh8MBQ1kaXNwbGF5Om5vbmU7ZAIHDw9kFgIfDAUNZGlzcGxheTpub25lO2QCCA8PZBYCHwwFDWRpc3BsYXk6bm9uZTtkAgkPEA9kFgIfDAUNZGlzcGxheTpub25lO2RkZAICD2QWAgIDDw8WAh8BBZMBPGI%2BSW5mb3JtYXRpb248L2I%2BOiBLUElzIGNhbiBub3QgYmUgY2FsY3VsYXRlZCBmb3IgbmV3IHByb2dyYW1zLiBJZiB5b3UgZmlsdGVyIGJ5IEtQSXMsIHRoZW4gb25seSBwcm9ncmFtcyB3aXRoIGNhbGN1bGF0ZWQgS1BJcyB3aWxsIGJlIGlkZW50aWZpZWQuZGQCGw8PFgQfAQUGU2VhcmNoHhFVc2VTdWJtaXRCZWhhdmlvcmhkZAIdDxYCHx0FBVJlc2V0ZAITDw8WAh8BBQdPdXIgdGlwZGQCFQ8PFgIfAwUhQ2xpY2sgaGVyZSB0byBoaWRlICJPdXIgdGlwIiBib3guZGQCFw9kFgJmD2QWAgIBD2QWBAIBD2QWAmYPZBYCAgkPDxYEHwQFB2J1dHRvbiAfBgICZGQCAg9kFgJmD2QWAgIJDw8WBB8EBQdidXR0b24gHwYCAmRkAhkPZBYEAgEPDxYCHwhoZGQCAw8PFgIfCGhkZAIfD2QWBAIBDxAPFgQfAQUTU2VsZWN0IGFsbCBwcm9ncmFtcx8TaBYCHwoFKFByb2dyYW1MaXN0UGFnZXMuU2VsZWN0QWxsUHJvZ3JhbXModGhpcylkZGQCAw8PFgYfAQUFQXBwbHkfAwUaQXBwbHkgdG8gc2VsZWN0ZWQgcHJvZ3JhbXMfIGgWAh8KBT5yZXR1cm4gUHJvZ3JhbUxpc3RQYWdlcy5idG5BcHBseVRvU2VsZWN0ZWRQcm9ncmFtc19DbGljayh0cnVlKWQCIQ9kFgICAQ8PFgIfCGhkFgoCAw8PFgIfAQUQRW50cmllcyBwZXIgcGFnZWRkAgcPEGRkFgECAWQCCQ8PFgIfCGhkFhQCAQ8PFgIfAQUEUGFnZWRkAgMPDxYCHwMFCkZpcnN0IHBhZ2VkFgJmDw8WAh8UBQpGaXJzdCBwYWdlZGQCBQ8PFgIfAwUNUHJldmlvdXMgcGFnZWQWAmYPDxYCHxQFDVByZXZpb3VzIHBhZ2VkZAIxDw8WAh8DBQlOZXh0IHBhZ2VkFgJmDw8WAh8UBQlOZXh0IHBhZ2VkZAIzDw8WAh8DBQ1QcmV2aW91cyBwYWdlZBYCZg8PFgIfFAUNUHJldmlvdXMgcGFnZWRkAjUPDxYCHwEFBGZyb21kZAI5Dw8WAh8BBQpHbyB0byBwYWdlZGQCOw8PFgIfAWVkZAI9Dw8WBB8DBQpHbyB0byBwYWdlHwkFJ34vQXBwX1RoZW1lcy9QYXNzaW9uL0ltYWdlcy90by1wYWdlLnBuZ2RkAj8PDxYEHx4FJFBsZWFzZSBlbnRlciBhIHBvc2l0aXZlIHBhZ2UgbnVtYmVyLh4UVmFsaWRhdGlvbkV4cHJlc3Npb24FC15bMC05XXsxLH0kZGQCCw8WAh8QBQExZAINDxYCHxBlZAIjD2QWAgIBD2QWGAIBDw8WAh8BBQZMZWdlbmRkZAIDDxYCHwcFCjxiPlNFTTwvYj5kAgUPDxYCHwEFOVNFTSBpcyBhbGxvd2VkIGZvciB0aGlzIHByb2dyYW0gd2l0aG91dCBhbnkgcmVzdHJpY3Rpb25zLmRkAgcPFgIfBwUKPGI%2BU0VNPC9iPmQCCQ8PFgIfAQVVU0VNIGlzIGFsbG93ZWQgZm9yIHRoaXMgcHJvZ3JhbSBidXQgd2l0aCByZXN0cmljdGlvbnMuIFJlYWQgcHJvZ3JhbSBpbmZvIGZvciBkZXRhaWxzLmRkAgsPFgIfBwUKPGI%2BU0VNPC9iPmQCDQ8PFgIfAQUQU0VNIG5vdCBhbGxvd2VkLmRkAg8PDxYCHwEFFlByb2R1Y3QgZGF0YSBhdmFpbGFibGVkZAIRDw8WAh8BBSZQcm9kdWN0IGRhdGEgYXZhaWxhYmxlIG9ubHkgb24gcmVxdWVzdGRkAhMPDxYCHwEFOVRoaXMgcHJvZ3JhbSBvZmZlcnMgYSBtb2JpbGUgYXBwIGZvciBzbWFydHBob25lcy90YWJsZXRzLmRkAhUPDxYCHwEFT1RoaXMgcHJvZ3JhbSBoYXMgbW9iaWxlIGNyZWF0aXZlIHNpemVzLCB3aGljaCBhcmUgb3B0aW1pc2VkIGZvciBtb2JpbGUgZGV2aWNlcy5kZAIXDw8WAh8BBTdUaGlzIHByb2dyYW0gaGFzIGEgbW9iaWxlIHdlYnNpdGUgb3IgcmVzcG9uc2l2ZSBkZXNpZ24uZGQCJQ9kFggCAQ8PFgIfAQUGRXhwb3J0ZGQCAw8QDxYEHwEFE0V4cG9ydCBhbGwgcHJvZ3JhbXMfE2dkZGRkAgUPEA8WBh8BBRRFeHBvcnQgc2VhcmNoIHJlc3VsdB8TaB8gaGRkZGQCBw8PFgYfAQUgQ29tcGxldGUgcHJvZ3JhbSBsaXN0IENTViBleHBvcnQfAwU9Q29tcGxldGUgcHJvZ3JhbSBsaXN0IENTViBleHBvcnQgaW5jbHVkaW5nIGRlZmF1bHQgcmF0ZXMgb25seR8kaGRkAhIPZBYCZg9kFgRmD2QWCgIBDw8WBh8DBQRIZWxwHwEFBEhlbHAfBQUQfi9IZWxwL0hlbHAuYXNweGRkAgUPDxYIHgZUYXJnZXQFBl9ibGFuax8DBQdDb250YWN0HwEFB0NvbnRhY3QfBQUPfi9IZWxwL0ZBUS5hc3B4ZGQCBw8PFggfJgUGX2JsYW5rHwMFFFRlcm1zIGFuZCBjb25kaXRpb25zHwEFFFRlcm1zIGFuZCBjb25kaXRpb25zHwUFdWh0dHA6Ly93d3cuYWZmaWxpLm5ldC9BRkZJL21lZGlhL0FGRklNZWRpYUxpYnJhcmllcy9Eb2N1bWVudHMvZGUtQVQvRm9vdGVyJTIwSXRlbS9hZmZpbGluZXRfQXVzdHJpYV9BR0JfUHVibGlzaGVyLnBkZmRkAgkPDxYIHyYFBl9ibGFuax8DBQ5Qcml2YWN5IHBvbGljeR8BBQ5Qcml2YWN5IHBvbGljeR8FBSdodHRwOi8vd3d3LmFmZmlsaS5uZXQvYXQvSW1wcmVzc3VtLmFzcHhkZAILDw8WAh8BBTRDb3B5cmlnaHQmbmJzcDsmY29weTsmbmJzcDsyMDE3LCZuYnNwO2FmZmlsaW5ldCBHbWJIZGQCAQ9kFgICAQ8PFgIfCQUuL0FwcF9UaGVtZXMvUGFzc2lvbi9JbWFnZXMvdW5pdGVkX2ludGVybmV0LnBuZ2RkAgEPZBYCZg8WAh8BBXk8Yj5Zb3UgbXVzdCBjb21wbGV0ZSB0aGUgZm9sbG93aW5nIGluIG9yZGVyIHRvIGZ1bGx5IGFjdGl2YXRlIHlvdXIgYWNjb3VudCBhbmQgYXBwZWFyIHRvIGFkdmVydGlzZXJzIG9uIHRoZSBwbGF0Zm9ybTo8L2I%2BZBgCBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WGgVDY3RsMDAkY3RsMDAkQ29udGVudFBsYWNlSG9sZGVyQ29udGVudCRGcmFtZTFDb250ZW50JHJhZENhdGVnb3J5VHJlZQUeY3RsMDAkY3RsMDAkUmFkVG9vbFRpcE1hbmFnZXIxBRVjdGwwMCRjdGwwMCRyd01haW5VcmwFHmN0bDAwJGN0bDAwJHJ3QWNjZXB0SW52aXRhdGlvbgU1Y3RsMDAkY3RsMDAkdWNQYWdlSGVhZGVyQmFyJGN0bEFjY291bnRMaXN0JHR2QWNjb3VudHMFR2N0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCRyYWRXaW5kb3dNdWx0aUFwcGx5BU9jdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkcmFkV2luZG93VGVybXNBbmRDb25kaXRpb25zBURjdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkY2JGaWx0ZXJTZXR0aW5ncwVIY3RsMDAkY3RsMDAkQ29udGVudFBsYWNlSG9sZGVyQ29udGVudCRGcmFtZTFDb250ZW50JGNiQ2hlY2tBbGxDYXRlZ29yaWVzBUFjdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkY2JJc0V4Y2x1c2l2ZQVBY3RsMDAkY3RsMDAkQ29udGVudFBsYWNlSG9sZGVyQ29udGVudCRGcmFtZTFDb250ZW50JGNiSXNUb3BNb2JpbGUFPmN0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCRjYlZvdWNoZXJzBTxjdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkY2JIYXNDUE0FQWN0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCRjYlByb2R1Y3REYXRhBUNjdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkY2JNb2JpbGVXZWJzaXRlBT9jdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkY2JNb2JpbGVBcHAFRWN0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCRjYk1vYmlsZUNyZWF0aXZlcwVHY3RsMDAkY3RsMDAkQ29udGVudFBsYWNlSG9sZGVyQ29udGVudCRGcmFtZTFDb250ZW50JGNiS1BJRmlsdGVyU2V0dGluZ3MFSWN0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCR1Y0tQSWVDUEMkY2JBY3RpdmF0ZWQFSmN0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCR1Y0tQSWVDUEMkY2JBbGxvd1JhbmdlBVRjdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkdWNLUElDYW5jZWxhdGlvblJhdGUkY2JBY3RpdmF0ZWQFVWN0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCR1Y0tQSUNhbmNlbGF0aW9uUmF0ZSRjYkFsbG93UmFuZ2UFU2N0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCR1Y0tQSUNvbnZlcnNpb25SYXRlJGNiQWN0aXZhdGVkBVRjdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkdWNLUElDb252ZXJzaW9uUmF0ZSRjYkFsbG93UmFuZ2UFU2N0bDAwJGN0bDAwJENvbnRlbnRQbGFjZUhvbGRlckNvbnRlbnQkRnJhbWUxQ29udGVudCR1Y0tQSVZhbGlkYXRpb25UaW1lJGNiQWN0aXZhdGVkBVRjdGwwMCRjdGwwMCRDb250ZW50UGxhY2VIb2xkZXJDb250ZW50JEZyYW1lMUNvbnRlbnQkdWNLUElWYWxpZGF0aW9uVGltZSRjYkFsbG93UmFuZ2UFJmN0bDAwJGN0bDAwJE5ld01haW5OYXZpZ2F0aW9uJE1haW5NZW51Dw9kBSJQcm9ncmFtc0FuZENyZWF0aXZlc1xQcm9ncmFtU2VhcmNoZB2JfCxlt%2BUW0BDT3dQ8JdQEtqdEWX%2F%2B2ndKkFkmWcth';
		
		$this->ctgr_postdata_common1 = 'ctl00%24ctl00%24ScriptManager1=ctl00%24ctl00%24ScriptManager1%7Cctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24btnSearch';
		$this->ctgr_postdata_common2 = '&__EVENTARGUMENT=&RadStyleSheetManager1_TSSM=%3BTelerik.Web.UI%2C%20Version%3D2012.3.1016.40%2C%20Culture%3Dneutral%2C%20PublicKeyToken%3D121fae78165ba3d4%3Aen-GB%3A8369618f-c29b-41d8-8350-a18333fae42b%3A53e1db5a%3Ad126a8ef%3A92753c09%3A45085116%3BTelerik.Web.UI.Skins%2C%20Version%3D2012.3.1016.40%2C%20Culture%3Dneutral%2C%20PublicKeyToken%3D121fae78165ba3d4%3Aen-GB%3Aad8247f5-bfa4-411f-8f02-a4a9c3cb5ca9%3Abf721433%3A70970e8a&ucPageHeaderBar_ctlAccountList_tvAccounts_ExpandState=en&ucPageHeaderBar_ctlAccountList_tvAccounts_SelectedNode=ucPageHeaderBar_ctlAccountList_tvAccountsn1&ucPageHeaderBar_ctlAccountList_tvAccounts_PopulateLog=&__LASTFOCUS=';
		$this->ctgr_postdata_common3 = '&__VIEWSTATEGENERATOR=DA1FC95B&ctl00_ctl00_RadToolTipManager1_ClientState=&ctl00_ctl00_rwMainUrl_ClientState=&ctl00_ctl00_rwAcceptInvitation_ClientState=&ctl00%24ctl00%24AffilinetpShift=1&ctl00%24ctl00%24AffilinetpSize=30&ctl00%24ctl00%24AffilinetParameter=&ctl00%24ctl00%24hidIsAuthenticated=false&ctl00%24ctl00%24hidPublisherId=811103&ctl00%24ctl00%24ucPageHeaderBar%24hidLanguage=en&ctl00_ctl00_ContentPlaceHolderContent_Frame1Content_radWindowMultiApply_ClientState=&ctl00_ctl00_ContentPlaceHolderContent_Frame1Content_radWindowTermsAndConditions_ClientState=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucFilterControl%24ddlCustomFilter=-1&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucFilterControl%24tbNewFilterName=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucFilterControl%24SavedPeriodHiddenField=True&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24tbxSearchQuery=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ddlSearchType=programs&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ddlPartnershipStatus=noRestrictions&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24cbFilterSettings=on&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24radCategoryTree_expanded=1000000000000000000000000000000000000000000000000000000000000000000000000000000000';
		$this->ctgr_postdata_common4 = '&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24radCategoryTree_selected=0000000000000000000000000000000000000000000000000000000000000000000000000000000000&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24radCategoryTree_scroll=0&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24radCategoryTree_viewstate=0&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24tbProductDataUpdateFrequencyLowerLimit=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ddlPaymentTypes=noRestrictions&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ddlTrackingType=noRestrictions&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24tbCookieLifeTimeLowerLimit=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ddlSEMPolicy=noRestrictions&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ddlCampaigns=noRestrictions&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24tbProgramLifeTime=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24hidUnit=%E2%82%AC&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24hidSelectedValue=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24hidSelectedLowerRangeValue=0&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24hidSelectedUpperRangeValue=10&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24hidSmallestStep=0.1&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24hidMaximumValueIsLimited=%26%238805%3B&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24hidMaximumValue=10&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24hidNumberOfDecimals=2&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIeCPC%24cbAllowRange=on&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24hidUnit=%25&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24hidSelectedValue=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24hidSelectedLowerRangeValue=0&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24hidSelectedUpperRangeValue=80&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24hidSmallestStep=1&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24hidMaximumValueIsLimited=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24hidMaximumValue=80&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24hidNumberOfDecimals=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPICancelationRate%24cbAllowRange=on&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24hidUnit=%25&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24hidSelectedValue=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24hidSelectedLowerRangeValue=0&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24hidSelectedUpperRangeValue=35&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24hidSmallestStep=1&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24hidMaximumValueIsLimited=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24hidMaximumValue=35&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24hidNumberOfDecimals=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIConversionRate%24cbAllowRange=on&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24hidUnit=days&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24hidSelectedValue=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24hidSelectedLowerRangeValue=0&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24hidSelectedUpperRangeValue=90&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24hidSmallestStep=1&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24hidMaximumValueIsLimited=%26%238805%3B&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24hidMaximumValue=90&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24hidNumberOfDecimals=&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24ucKPIValidationTime%24cbAllowRange=on&ctl00%24ctl00%24ContentPlaceHolderContent%24Frame1Content%24HiddenFieldViewChange=xmlK&__ASYNCPOST=true&RadAJAXControlID=ctl00_ctl00_ContentPlaceHolderContent_RadAjaxManager2Name&';
	}

}

