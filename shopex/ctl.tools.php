<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_tools extends adminpage
{

		var $workground = "tools";

		function welcome( )
		{
				$this->page( "system/tools/welcome.html" );
		}

		function index( )
		{
				$this->page( "system/tools/index.html" );
		}

		function seo( )
		{
				$this->workground = "sale";
				$this->path[] = array( "text" => "SEO����" );
				$this->page( "system/tools/seo.html" );
		}

		function seoedit( )
		{
				$this->begin( "index.php?ctl=system/tools&act=seo" );
				$GLOBALS['_POST']['setting']['site.tax_ratio'] = $_POST['setting']['site.tax_ratio'] / 100;
				$storager = $this->system->loadmodel( "system/storager" );
				$this->end( $this->settingedit( ), __( "�޸ĳɹ�" ) );
		}

		function _modified( $src, $key )
		{
				if ( isset( $src[$key] ) && $src[$key] != $this->system->getconf( $key ) )
				{
						return true;
				}
				return false;
		}

		function settingedit( )
		{
				foreach ( $GLOBALS['_POST']['_set_'] as $key => $type )
				{
						if ( $type == "bool" )
						{
								$GLOBALS['_POST']['setting'][$key] = $_POST['setting'][$key] ? true : false;
						}
				}
				if ( $this->_modified( $_POST['setting'], "site.stripHtml" ) )
				{
						$frontend = $this->system->loadmodel( "system/frontend" );
						$frontend->clear_compiled_tpl( );
				}
				$this->system->setconf( "readingGlass", $_POST['readingGlass'] ? 1 : 0 );
				if ( isset( $_POST['setting']['system.seo.emuStatic'] ) && $_POST['setting']['system.seo.emuStatic'] )
				{
						$svinfo = $this->system->loadmodel( "utility/serverinfo" );
						$url = parse_url( $this->system->base_url( ) );
						$code = substr( md5( time( ) ), 0, 6 );
						$content = $svinfo->dohttpquery( $url['path']."/_test_rewrite=1&s=".$code."&a.html" );
						if ( false && !strpos( $content, "[*[".md5( $code )."]*]" ) )
						{
								if ( false === strpos( strtolower( $_SERVER['SERVER_SOFTWARE'] ), "apache" ) )
								{
										trigger_error( __( "���ķ���������apache,�޷�ʹ��htaccess�ļ������ֶ�����rewrite�������޷�����α��̬" ), E_USER_ERROR );
								}
								if ( file_exists( BASE_DIR."/".ACCESSFILENAME ) )
								{
										trigger_error( __( "����ϵͳ������Ч��".ACCESSFILENAME.", �޷�����α��̬" ), E_USER_ERROR );
								}
								else if ( $content = file_get_contents( BASE_DIR."/root.htaccess" ) )
								{
										$content = preg_replace( "/RewriteBase\\s+.*\\//i", "RewriteBase ".$url['path'], $content );
										if ( file_put_contents( BASE_DIR."/".ACCESSFILENAME, $content ) )
										{
												$content = $svinfo->dohttpquery( $url['path']."/_test_rewrite=1&s=".$code."&a.html" );
												if ( !strpos( $content, "[*[".md5( $code )."]*]" ) )
												{
														unlink( BASE_DIR."/".ACCESSFILENAME );
														trigger_error( __( "����ϵͳ��֧��apache��".ACCESSFILENAME.",����α��̬ʧ��." ), E_USER_ERROR );
												}
										}
										else
										{
												trigger_error( __( "�޷��Զ�����".ACCESSFILENAME.",������Ȩ������,����α��̬ʧ��" ), E_USER_ERROR );
										}
								}
								else
								{
										trigger_error( __( "ϵͳ��֧��rewrite,ͬʱ��ȡԭʼroot.htaccess�ļ�������Ŀ��".ACCESSFILENAME."�ļ�,����޷�����α��̬" ), E_USER_ERROR );
								}
								trigger_error( __( "��֧��rewrite,����" ), E_USER_ERROR );
						}
				}
				foreach ( $GLOBALS['_POST']['setting'] as $k => $v )
				{
						if ( $this->system->setconf( $k, stripslashes( $v ) ) )
						{
								continue;
						}
						trigger_error( $k.__( "���ô���" ), E_USER_ERROR );
						return false;
				}
				return true;
		}

		function sitemaps( )
		{
				$this->path[] = array( "text" => "���������Ż�" );
				$this->workground = "sale";
				$this->pagedata['url'] = $this->system->realurl( "sitemaps", "catalog", null, "xml", $this->system->base_url( ) );
				$this->page( "system/tools/sitemaps.html" );
		}

		function editvalidtime( )
		{
				$timer = intval( $_POST['validtime'] );
				$this->begin( "index.php?ctl=system/tools&act=createLink" );
				if ( $this->system->setconf( "site.refer_timeout", $timer ) )
				{
						$this->end( true, "�޸ĳɹ�" );
				}
				else
				{
						$this->end( false, "�޸�ʧ��" );
				}
		}

		function createlink( )
		{
				$this->path[] = array( "text" => "վ���ƹ�����" );
				$this->workground = "sale";
				$timer = $this->system->getconf( "site.refer_timeout" );
				$this->pagedata['base_url'] = $this->system->base_url( );
				$this->pagedata['validtime'] = $timer;
				$this->page( "system/tools/createlink.html" );
		}

		function footedit( )
		{
				$this->path[] = array( "text" => "��ҳ�ײ���Ϣ" );
				$this->pagedata['footEdit'] = stripslashes( $this->system->getconf( "system.foot_edit" ) );
				$this->page( "system/tools/footEdit.html" );
		}

		function savefoot( )
		{
				if ( $this->system->setconf( "system.foot_edit", stripslashes( $_POST['footEdit'] ) ) )
				{
						$this->splash( "success", "index.php?ctl=system/tools&act=footEdit", __( "����ɹ�" ) );
				}
		}

		function errorpage( $code )
		{
				$this->path[] = array( "text" => "ϵͳ����ҳ����" );
				$templete = "errorpage.html";
				switch ( $code )
				{
				case "404" :
						$this->pagedata['pagename'] = "�޷��ҵ�ҳ��";
						$this->pagedata['code'] = "404";
						$this->pagedata['errorpage'] = stripslashes( $this->system->getconf( "errorpage.p404" ) );
						break;
				case "500" :
						$this->pagedata['pagename'] = "ϵͳ��������";
						$this->pagedata['code'] = "500";
						$this->pagedata['errorpage'] = stripslashes( $this->system->getconf( "errorpage.p500" ) );
						break;
				case "searchempty" :
						$this->pagedata['pagename'] = "����Ϊ��ʱ��ʾ����";
						$this->pagedata['code'] = "searchempty";
						$this->pagedata['errorpage'] = stripslashes( $this->system->getconf( "errorpage.searchempty" ) );
						$templete = "searchempty.html";
				}
				$this->page( "system/tools/".$templete );
		}

		function saveerrorpage( )
		{
				switch ( $_POST['code'] )
				{
				case "404" :
						$this->system->setconf( "errorpage.p404", stripslashes( $_POST['errorpage'] ) );
						break;
				case "500" :
						$this->system->setconf( "errorpage.p500", stripslashes( $_POST['errorpage'] ) );
						file_put_contents( HOME_DIR."/upload/error500.html", stripslashes( $_POST['errorpage'] ) );
						break;
				case "searchempty" :
						$this->system->setconf( "errorpage.searchempty", stripslashes( $_POST['errorpage'] ) );
				}
				$this->splash( "success", "index.php?ctl=system/tools&act=errorpage&p[0]=".$_POST['code'], __( "��ǰҳ�汣��ɹ�" ) );
		}

}

?>
