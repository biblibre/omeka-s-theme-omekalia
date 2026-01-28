<?php 
/*****
 * DisplaySelectedValues Helper
 * Last Update : 2025-11-10
 *****/
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;

class DisplaySelectedValues extends AbstractHelper
{

	/**
	 * Returns a Resource Values HTML.
	 *
	 * @param object $resource The resource to get values from.
	 * @param string $properties The metadata to display.
	 * @param array $options Additional options for metadata display. {
	 * 		@var bool   'showValueAnnotations' Display value annotations or not.
	 * 		@var string 'propertiesForSearch' Add search link to this metadata.
	 * 		@var string 'multiValuesSeparator' Multivalues Separator.
	 * 		@var bool   'stripTags' Strip tags of values.
	 * }
	 * @return string
	 */

	public function __invoke($resource, $properties, array $options = [] )
	{
		if (!$resource || !$properties) {
			return '';
		}

		if ( isset($options['showValueAnnotations']) ) {
			$showValueAnnotations = $options['showValueAnnotations'];
		} else {
			$showValueAnnotations = false;
		}

		if ( isset($options['propertiesForSearch']) ) {
			$propertiesForSearch = $options['propertiesForSearch'];
		} else {
			$propertiesForSearch = '';
		}

		if ( isset($options['multiValuesSeparator']) ) {
			$multiValuesSeparator = $options['multiValuesSeparator'];
		} else {
			$multiValuesSeparator = ' | ';
		}

		if ( isset($options['stripTags']) ) {
			$stripTags = $options['stripTags'];
		} else {
			$stripTags = false;
		}

		$view = $this->getView();
		$translate = $view->plugin('translate');
		$escape = $view->plugin('escapeHtml');
		$lang = $view->lang();

		if ($showValueAnnotations) $showValueAnnotations = (bool) $view->siteSetting('show_value_annotations', false);
		$showLocale = (bool) $view->siteSetting('show_locale_label', true);

		if (gettype($properties) == 'string') $properties = explode(',', str_replace("\n", ',', $properties) );
		$propertiesToDisplay = array_map('trim', $properties);

		if (gettype($propertiesForSearch) == 'string') $propertiesForSearch = explode(',', str_replace("\n", ',', $propertiesForSearch) );
		$propertiesForSearch = array_map('trim', $propertiesForSearch);

		$metadataContent = '';
		$metadataGroup = false;

		if( isset($propertiesToDisplay) && count($propertiesToDisplay) > 0 ):
			foreach ($propertiesToDisplay as $propertyValue):

				if ($resource->value($propertyValue)):

					$propertyData_tmp = $resource->values();
					$propertyData = $propertyData_tmp[$propertyValue];

					if ($propertyData):

						if ($propertyData['alternate_label']):
							$propertyLabel = $escape($propertyData['alternate_label']);
						else:
							$propertyLabel_tmp = $propertyData['property'];
							$propertyLabel = $escape( $translate( $propertyLabel_tmp->label() ) );
						endif;

						$propertyClass = $escape( str_replace(['_', ':'], '-', $propertyValue) );
						$metadataContent .= '<div class="property resource__property '.$propertyClass.'">';
						$metadataContent .= '<dt class="label">'.$propertyLabel.$translate(': ').'</dt>';

						$searchLinkUrl = '';

						if ($propertiesForSearch) {
							if ( in_array( $propertyValue, $propertiesForSearch ) ) {
								$searchLinkUrl = $view->site->url().'/search?limit['.substr( strrchr($propertyValue, ":"), 1).'][0]=';
							}
						}

						$propertyDataValues = array();
						foreach ($propertyData['values'] as $value) {
							$valueType = $value->type();
							$valueLang = $value->lang();
							$valueAnnotation = $value->valueAnnotation();
							$propertyDataValuesTemp = $value->asHtml();

							if ($stripTags) {
								$propertyDataValuesTemp = strip_tags($propertyDataValuesTemp);
							}

							if ($searchLinkUrl) {
								$propertyDataValueNoHtml = strip_tags($propertyDataValuesTemp);
								$searchLinkUrlComplete = $escape($searchLinkUrl.urlencode( $propertyDataValueNoHtml ));

								if (preg_match( '/<a\s+[^>]*href=["\'][^"\']+["\'][^>]*>/i', $propertyDataValuesTemp, $propertyDataValueUrl )) {
									// la chaine $propertyDataValuesTemp contient un lien web
									$propertyDataValuesTemp = '<a href="'.$searchLinkUrlComplete.'">'.$propertyDataValueNoHtml.'<i class="o-icon-search"></i></a>';
									$propertyDataValuesTemp .= '&nbsp;<span class="uri-value-link--icon">'.$propertyDataValueUrl[0].'</a></span>';
								} else {
									// pas de lien dans la chaine
									$propertyDataValuesTemp = '<a href="'.$searchLinkUrlComplete.'">'.$propertyDataValuesTemp.'<i class="o-icon-search"></i></a>';
								}

							}

							if ($valueAnnotation && $showValueAnnotations):
								$propertyDataValuesTemp .= '<a href="#" class="'.(('expanded' === $showValueAnnotations) ? 'collapse' : 'expand').'" aria-label="'.(('expanded' === $showValueAnnotations) ? $escape($translate('Collapse')) : $escape($translate('Expand'))).'">
									<span class="has-annotation o-icon-annotation" role="img" title="'.$escape($translate('Has annotation')).'" aria-label="'.$escape($translate('Has annotation')).'"></span>
								</a>
								<div class="collapsible annotation">'.$valueAnnotation->displayValues().'</div>';
							endif;

							if ($showLocale && $valueLang):
								$htmlLang = '<span class="language">'.$valueLang.'</span> ';
							else:
								$htmlLang = '';
							endif;

							$classDD = ['value'];
							if ('resource' == $valueType || strpos($valueType, 'resource') !== false) {
								$classDD[] = 'resource';
								$classDD[] = $escape($value->valueResource()->resourceName());
							} elseif ('uri' == $valueType) {
								$classDD[] = 'uri';
							}
							if (!$value->isPublic()) {
								$classDD[] = 'private';
							}
							$htmlOpenTag = '<dd class="'.implode(' ', $classDD).'"'.(($valueLang)? ' lang="'.$escape($valueLang).'"':'').'>';
							$htmlCloseTag = '</dd>';

							$propertyDataValues[] = $htmlOpenTag.$htmlLang.$propertyDataValuesTemp.$htmlCloseTag;

						}

						$metadataContent .= implode( ' ', $propertyDataValues );
						//$view->trigger('view.show.value', ['value' => $value]);

						$metadataContent .= '</div>';
					endif;

				elseif ($propertyValue == '#sep#'):

					$metadataContent .= '<hr class="resource__divider"/>';

				elseif ($propertyValue == '#group#'):

					if ($metadataGroup) $metadataContent .= '</div>';
					$metadataContent .= '<div class="data__group">';
					$metadataGroup = true;

				elseif ($propertyValue == '#/group#'):

					if ($metadataGroup) $metadataContent .= '</div>';
					$metadataGroup = false;

				elseif ( substr($propertyValue, 0, 7) == '#title#'):

					$metadataContent .= '<p><strong>'.(substr( $propertyValue, 7)).'</strong></p>';

				endif;

			endforeach;

			if ($metadataGroup) {$metadataContent .= '</div>';};

			if ($metadataContent):
				$metadataContent = '<dl style="--separator:\''.$multiValuesSeparator.'\'">'.$metadataContent.'</dl>';
			endif;

		endif;
		
		return $metadataContent;

	}

}
