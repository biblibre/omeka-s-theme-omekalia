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
	 * @param bool   $annotations Display value annotations.
	 * @return string
	 */
	public function __invoke($resource,$properties, $showValueAnnotations = false)
	{
		if (!$resource || !$properties) {
			return '';
		}

		$view = $this->getView();
		$translate = $view->plugin('translate');
		$escape = $view->plugin('escapeHtml');

		if ($showValueAnnotations) $showValueAnnotations = (bool) $view->siteSetting('show_value_annotations', false);

		$propertiesToDisplay = explode(',', str_replace("\n", ',', $properties) );
		$propertiesToDisplay = array_map('trim', $propertiesToDisplay);

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

						$propertyClass = str_replace(':', '-', $propertyValue);
						$metadataContent .= '<div class="property resource__property '.$propertyClass.'">';
						$metadataContent .= '<dt class="label">'.$propertyLabel.$translate(': ').'</dt>';

						$propertyDataValues = array();
						foreach ($propertyData['values'] as $value) {
							$valueAnnotation = $value->valueAnnotation();
							//$propertyDataValues[] = strip_tags($value->asHtml());
							$propertyDataValuesTemp = $value->asHtml();
							/*
							if ( $propertyValue == 'dcterms:isReferencedBy' ) {
								$propertyDataValuesTemp = preg_replace( '/>(https?:\/\/gallica\.bnf\.fr\/[^\s]+)</', '>Gallica<', $propertyDataValuesTemp );
								$propertyDataValuesTemp = preg_replace( '/>(https?:\/\/[^\s]+\.bnf\.fr\/[^\s]+)</', '>BnF<', $propertyDataValuesTemp );
								$propertyDataValuesTemp = preg_replace( '/>(https?:\/\/[^\s]+\.sudoc\.fr\/[^\s]+)</', '>Sudoc<', $propertyDataValuesTemp );
								$propertyDataValuesTemp = preg_replace( '/>(https?:\/\/[^\s]+calames\.abes\.fr\/[^\s]+)</', '>Calames<', $propertyDataValuesTemp );
							}
							*/
							$propertyDataValues[] = $propertyDataValuesTemp;
						}

						$metadataContent .= '<dd class="resource__value value">';
						$metadataContent .= implode( ' | ', $propertyDataValues );

						if ($valueAnnotation && $showValueAnnotations):
							$metadataContent .= '<a href="#" class="'.(('expanded' === $showValueAnnotations) ? 'collapse' : 'expand').'" aria-label="'.(('expanded' === $showValueAnnotations) ? $escape($translate('Collapse')) : $escape($translate('Expand'))).'">
								<span class="has-annotation o-icon-annotation" role="img" title="'.$escape($translate('Has annotation')).'" aria-label="'.$escape($translate('Has annotation')).'"></span>
							</a>
							<div class="collapsible annotation">'.$valueAnnotation->displayValues().'</div>';
						endif;

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
