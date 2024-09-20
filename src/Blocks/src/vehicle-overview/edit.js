/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, BlockControls, AlignmentControl } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param {Object}   param0
 * @param {Object}   param0.attributes
 * @param {string}   param0.attributes.textAlign
 * @param {Function} param0.setAttributes
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes: { textAlign }, setAttributes }) {
	// If the text align attribute is set, apply the correct class.
	const blockProps = useBlockProps({
		className: textAlign ? 'has-text-align-' + textAlign : '',
	});

	// Sample data to display in the editor
	const vehicleDetails = [
		{ label: 'Exterior', value: 'Red', icon: 'fas fa-paint-brush' },
		{ label: 'Interior', value: 'Beige', icon: 'fas fa-couch' },
		{ label: 'Mileage', value: '62,172 miles', icon: 'fas fa-tachometer-alt' },
		{ label: 'Fuel Type', value: 'Gas', icon: 'fas fa-gas-pump' },
		{ label: 'Fuel Efficiency', value: '24 city / 35 highway', icon: 'fas fa-gas-pump' },
		{ label: 'Transmission', value: 'Automatic', icon: 'fas fa-cogs' },
		{ label: 'Drivetrain', value: 'FWD', icon: 'fas fa-car' },
		{ label: 'Engine', value: '2.2L Inline-4 Gas', icon: 'fas fa-car-engine' },
		{ label: 'Location', value: 'South Hackensack, NJ', icon: 'fas fa-map-marker-alt' },
		{ label: 'Listed', value: 'Listed 4 days ago', icon: 'fas fa-calendar-alt' },
		{ label: 'VIN', value: '1G1ZS52F95F202932', icon: 'fas fa-car' },
		{ label: 'Stock Number', value: '202932-32', icon: 'fas fa-hashtag' },
	];

	// Split the details into two columns
	const leftColumnDetails = vehicleDetails.filter((_, index) => index % 2 === 0);
	const rightColumnDetails = vehicleDetails.filter((_, index) => index % 2 !== 0);

	return (
		<>
			<BlockControls group="block">
				<AlignmentControl
					value={textAlign}
					onChange={(nextAlign) => {
						setAttributes({ textAlign: nextAlign });
					}}
				/>
			</BlockControls>
			<div {...blockProps}>
				<div className="wp-block-columns">
					<div className="wp-block-column">
						<ul className="wp-block-list">
							{leftColumnDetails.map((detail, index) => (
								<li key={index}>
									<i className={`${detail.icon} mr-2-5`} aria-hidden="true"></i>
									<strong>{detail.label}:</strong> {detail.value}
								</li>
							))}
						</ul>
					</div>
					<div className="wp-block-column">
						<ul className="wp-block-list">
							{rightColumnDetails.map((detail, index) => (
								<li key={index}>
									<i className={`${detail.icon} mr-2-5`} aria-hidden="true"></i>
									<strong>{detail.label}:</strong> {detail.value}
								</li>
							))}
						</ul>
					</div>
				</div>
			</div>
		</>
	);
}