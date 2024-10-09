/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { SelectControl } from '@wordpress/components';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */

export default function Edit( props ) {
	const { attributes, setAttributes } = props;
	const [ error, setError ] = useState( [] );
	const [ types, setTypes ] = useState( [] );

	useEffect( () => {
		( async () => {
			await apiFetch( {
				path: '/wow/v1/type',
			} ).then(
				( response ) => {
					setTypes( response.results );
				},
				( error ) => {
					setError( error );
				}
			);
		} )();
	}, [] );

	useEffect( () => {
		( async () => {
			await apiFetch( {
				path: '/wow/v1/poke_info',
				method: 'POST',
				data: { url: attributes.pokemon_types },
			} ).then(
				( response ) => {
					setAttributes( { pokes_html: response } );
				},
				( error ) => {
					setError( error );
				}
			);
		} )();
	}, [] );

	const options = [];
	if ( types ) {
		types.forEach( ( type ) => {
			options.push( { value: type.url, label: type.name } );
		} );
	} else {
		options.push( { value: 0, label: 'Loading...' } );
	}

	function setPokemonType( pokemonType ) {
		setAttributes( { pokemon_types: pokemonType } );
		options.forEach( function ( entry ) {
			if ( entry.value === pokemonType ) {
				setAttributes( { type_name: entry.label } );
			}
		} );

		const dat = apiFetch( {
			path: '/wow/v1/poke_info',
			method: 'POST',
			data: { url: pokemonType },
		} ).then(
			( response ) => {
				setAttributes( { pokes_html: response } );
			},
			( error ) => {
				setError( error );
			}
		);
	}

	return (
		<SelectControl
			label="Select a pokemon type"
			options={ options }
			value={ attributes.pokemon_types }
			onChange={ ( newType ) => setPokemonType( newType ) }
		/>
	);
}
