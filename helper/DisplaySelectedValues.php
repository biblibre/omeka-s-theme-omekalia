<?php 
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

		if ($showValueAnnotations) $showValueAnnotations = (bool) $view->siteSetting('show_value_annotations', false);

		$propertiesToDisplay = explode(',', str_replace("\n", ',', $properties) );
		$propertiesToDisplay = array_map('trim', $propertiesToDisplay);

		$propertiesForSearch = explode(',', str_replace("\n", ',', $propertiesForSearch) );
		$propertiesForSearch = array_map('trim', $propertiesForSearch);

		$metadataContent = '';

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
							$valueAnnotation = $value->valueAnnotation();
							//$propertyDataValues[] = strip_tags($value->asHtml());
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
//									$propertyDataValuesTemp .= '<a class="uri-value-link" target="_blank" href="'.$propertyDataValueUrl[0].'" title="Ouvre un nouvel onglet"><i class="fas fa-external-link-square-alt"></i></a>';
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


							$propertyDataValues[] = $propertyDataValuesTemp;

						}

						$metadataContent .= '<dd class="resource__value value">';
						$metadataContent .= implode( $multiValuesSeparator, $propertyDataValues );
						$view->trigger('view.show.value', ['value' => $value]);
						$metadataContent .= '</dd>';

						$metadataContent .= '</div>';
					endif;

				elseif ($propertyValue == '#sep#'):

					$metadataContent .= '<hr class="resource__divider"/>';

				endif;

			endforeach;

			if ($metadataContent):
				$metadataContent = '<dl>'.$metadataContent.'</dl>';
			endif;

		endif;
		
		return $metadataContent;

	}

}
