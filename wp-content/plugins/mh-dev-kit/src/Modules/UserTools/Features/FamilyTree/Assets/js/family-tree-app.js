( function () {
	function parseTree( raw ) {
		try {
			return JSON.parse( raw || '{}' );
		} catch ( e ) {
			return { root: { id: 'root', name: 'Root', children: [] } };
		}
	}

	function mountApps() {
		if ( typeof Vue === 'undefined' ) {
			return;
		}

		document.querySelectorAll( '.mhdk-family-tree-app' ).forEach( function ( el ) {
			if ( el.dataset.mounted ) {
				return;
			}

			el.dataset.mounted = '1';

			var mountEl = el.querySelector( '.mhdk-family-tree-mount' );
			var editable = el.getAttribute( 'data-editable' ) === '1';
			var restUrl = el.getAttribute( 'data-rest-url' );
			var nonce = el.getAttribute( 'data-nonce' );
			var initial = parseTree( el.getAttribute( 'data-tree' ) );

			var TreeNode = {
				name: 'TreeNode',
				props: [ 'node', 'editable' ],
				emits: [ 'add-child' ],
				template:
					'<li class="mhdk-tree-node">' +
					'<span class="mhdk-tree-node-name">{{ node.name }}</span>' +
					'<button v-if="editable" type="button" class="button-link" @click="$emit(\'add-child\', node)">+ child</button>' +
					'<ul v-if="node.children && node.children.length"><tree-node v-for="child in node.children" :key="child.id" :node="child" :editable="editable" @add-child="$emit(\'add-child\', $event)" /></ul>' +
					'</li>',
			};

			Vue.createApp( {
				components: { TreeNode: TreeNode },
				data: function () {
					return {
						tree: initial,
						editable: editable,
						status: '',
					};
				},
				methods: {
					addChild: function ( parent ) {
						if ( ! parent.children ) {
							parent.children = [];
						}

						parent.children.push( {
							id: 'n' + Date.now(),
							name: 'New person',
							children: [],
						} );
					},
					save: function () {
						var self = this;

						if ( ! this.editable || ! restUrl ) {
							return;
						}

						this.status = 'Saving…';

						fetch( restUrl, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-WP-Nonce': nonce,
							},
							body: JSON.stringify( { tree: this.tree } ),
						} )
							.then( function ( res ) {
								return res.json();
							} )
							.then( function () {
								self.status = 'Saved';
							} )
							.catch( function () {
								self.status = 'Save failed';
							} );
					},
				},
				template:
					'<div class="mhdk-family-tree-ui">' +
					'<ul class="mhdk-family-tree-root"><tree-node :node="tree.root" :editable="editable" @add-child="addChild" /></ul>' +
					'<p v-if="editable"><button type="button" class="button" @click="save">Save tree</button> <span>{{ status }}</span></p>' +
					'</div>',
			} ).mount( mountEl );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', mountApps );
	} else {
		mountApps();
	}
} )();
