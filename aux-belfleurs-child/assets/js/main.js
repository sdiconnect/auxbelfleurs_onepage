/**
 * Aux Bêl'fleurs — JS unique.
 *
 * - Leaflet chargé en différé à l'approche de la section carte.
 * - Marqueurs + popups (nom, adresse, tél, itinéraire).
 * - Recherche ville / code postal (filtre liste + carte).
 * - « Autour de moi » : géolocalisation + tri par distance.
 * - Survol d'une carte de la liste => le marqueur correspondant s'anime.
 *
 * Sans dépendance tant que Leaflet n'est pas chargé.
 */
(function () {
	'use strict';

	var CFG = window.ABF_MAP || {};
	var stores = CFG.stores || [];
	var i18n = CFG.i18n || {};

	var mapEl = document.getElementById('abf-map');
	var listEl = document.getElementById('abf-store-list');
	var searchInput = document.getElementById('abf-search-input');
	var nearBtn = document.getElementById('abf-near-me');
	var emptyEl = document.getElementById('abf-list-empty');

	if (!mapEl) {
		return;
	}

	var map = null;
	var markers = {};          // id => L.marker
	var leafletLoading = false;

	/* ------------------------------------------------------------------ */
	/* Chargement différé de Leaflet                                       */
	/* ------------------------------------------------------------------ */

	function loadLeaflet() {
		if (leafletLoading || window.L) {
			if (window.L) { initMap(); }
			return;
		}
		leafletLoading = true;

		var css = document.createElement('link');
		css.rel = 'stylesheet';
		css.href = CFG.leafletCss;
		document.head.appendChild(css);

		var js = document.createElement('script');
		js.src = CFG.leafletJs;
		js.async = true;
		js.onload = initMap;
		js.onerror = function () {
			mapEl.innerHTML = '<p class="abf-noscript">' + (i18n.noResult || 'Carte indisponible.') + '</p>';
		};
		document.head.appendChild(js);
	}

	// Charge Leaflet quand la section carte approche.
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					loadLeaflet();
					io.disconnect();
				}
			});
		}, { rootMargin: '300px' });
		io.observe(mapEl);
	} else {
		loadLeaflet();
	}

	/* ------------------------------------------------------------------ */
	/* Initialisation de la carte                                          */
	/* ------------------------------------------------------------------ */

	function initMap() {
		if (map || !window.L) { return; }

		map = L.map(mapEl, { scrollWheelZoom: false });

		L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
		}).addTo(map);

		// Réactive la molette après un clic sur la carte (confort mobile).
		map.on('click', function () { map.scrollWheelZoom.enable(); });

		var bounds = [];
		stores.forEach(function (s) {
			if (s.lat == null || s.lng == null) { return; }
			var marker = L.marker([s.lat, s.lng]).addTo(map);
			marker.bindPopup(popupHtml(s));
			marker.on('click', function () { highlightStore(s.id, true); });
			markers[s.id] = marker;
			bounds.push([s.lat, s.lng]);
		});

		if (bounds.length) {
			map.fitBounds(bounds, { padding: [30, 30], maxZoom: 12 });
		} else {
			map.setView(CFG.center || [47.3, 6.15], CFG.zoom || 9);
		}
	}

	function popupHtml(s) {
		var addr = [s.adr, ((s.cp || '') + ' ' + (s.ville || '')).trim()]
			.filter(Boolean).join('<br>');
		var route = 'https://www.openstreetmap.org/directions?to=' + encodeURIComponent(s.lat + ',' + s.lng);
		var html = '<div class="abf-popup"><h4>' + esc(s.nom) + '</h4>';
		if (addr) { html += '<p>' + addr + '</p>'; }
		if (s.tel) { html += '<p><a href="tel:' + telHref(s.tel) + '">' + esc(s.tel) + '</a></p>'; }
		html += '<a class="abf-btn abf-btn--small" target="_blank" rel="noopener" href="' + route + '">' +
			(i18n.route || 'Itinéraire') + '</a></div>';
		return html;
	}

	/* ------------------------------------------------------------------ */
	/* Interaction liste <-> carte                                         */
	/* ------------------------------------------------------------------ */

	var listItems = listEl ? Array.prototype.slice.call(listEl.querySelectorAll('.abf-store')) : [];

	function highlightStore(id, openPopup) {
		listItems.forEach(function (li) {
			li.classList.toggle('is-active', parseInt(li.getAttribute('data-id'), 10) === id);
		});
		var marker = markers[id];
		if (marker && map) {
			bounce(marker);
			if (openPopup) {
				map.setView(marker.getLatLng(), Math.max(map.getZoom(), 12), { animate: true });
				marker.openPopup();
			}
		}
	}

	function bounce(marker) {
		var el = marker._icon;
		if (!el) { return; }
		el.style.transition = 'transform .2s';
		el.style.transformOrigin = 'bottom center';
		el.style.transform += ' scale(1.35)';
		setTimeout(function () {
			el.style.transform = el.style.transform.replace(' scale(1.35)', '');
		}, 250);
	}

	listItems.forEach(function (li) {
		var id = parseInt(li.getAttribute('data-id'), 10);
		li.addEventListener('mouseenter', function () {
			if (markers[id]) { bounce(markers[id]); }
		});
		li.addEventListener('click', function (e) {
			// On laisse les liens (tél, itinéraire) fonctionner normalement.
			if (e.target.closest('a')) { return; }
			if (!map) { loadLeaflet(); }
			highlightStore(id, true);
			mapEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		});
	});

	/* ------------------------------------------------------------------ */
	/* Recherche ville / code postal                                       */
	/* ------------------------------------------------------------------ */

	if (searchInput) {
		searchInput.addEventListener('input', debounce(function () {
			var q = normalize(searchInput.value.trim());
			var visibleBounds = [];
			var count = 0;

			listItems.forEach(function (li) {
				var hay = normalize(li.getAttribute('data-search') || '');
				var match = q === '' || hay.indexOf(q) !== -1;
				li.classList.toggle('is-hidden', !match);
				if (match) {
					count++;
					var lat = parseFloat(li.getAttribute('data-lat'));
					var lng = parseFloat(li.getAttribute('data-lng'));
					if (!isNaN(lat) && !isNaN(lng)) { visibleBounds.push([lat, lng]); }
				}
			});

			// Masque les groupes de département devenus vides.
			Array.prototype.forEach.call(listEl.querySelectorAll('.abf-dep-group'), function (g) {
				var anyVisible = g.querySelector('.abf-store:not(.is-hidden)');
				g.style.display = anyVisible ? '' : 'none';
			});

			if (emptyEl) {
				emptyEl.hidden = count !== 0;
				emptyEl.textContent = i18n.noResult || 'Aucune boutique ne correspond.';
			}

			if (map && visibleBounds.length) {
				map.fitBounds(visibleBounds, { padding: [30, 30], maxZoom: 13 });
			}
		}, 180));
	}

	/* ------------------------------------------------------------------ */
	/* « Autour de moi »                                                   */
	/* ------------------------------------------------------------------ */

	if (nearBtn && 'geolocation' in navigator) {
		nearBtn.hidden = false;
		nearBtn.addEventListener('click', function () {
			nearBtn.disabled = true;
			var original = nearBtn.textContent;
			nearBtn.textContent = i18n.locating || 'Localisation…';

			navigator.geolocation.getCurrentPosition(function (pos) {
				var lat = pos.coords.latitude;
				var lng = pos.coords.longitude;
				sortByDistance(lat, lng);
				if (!map) { loadLeaflet(); }
				var settle = setInterval(function () {
					if (map) {
						clearInterval(settle);
						L.circleMarker([lat, lng], { radius: 8, color: '#12726e', fillColor: '#12726e', fillOpacity: .6 })
							.addTo(map).bindPopup('Vous êtes ici');
						map.setView([lat, lng], 10, { animate: true });
					}
				}, 200);
				nearBtn.disabled = false;
				nearBtn.textContent = original;
			}, function () {
				alert(i18n.denied || 'Géolocalisation indisponible.');
				nearBtn.disabled = false;
				nearBtn.textContent = original;
			}, { enableHighAccuracy: false, timeout: 8000, maximumAge: 600000 });
		});
	}

	function sortByDistance(lat, lng) {
		var withDist = listItems.map(function (li) {
			var sLat = parseFloat(li.getAttribute('data-lat'));
			var sLng = parseFloat(li.getAttribute('data-lng'));
			var d = (isNaN(sLat) || isNaN(sLng)) ? Infinity : haversine(lat, lng, sLat, sLng);
			return { li: li, d: d };
		});
		withDist.sort(function (a, b) { return a.d - b.d; });

		// Aplatit la liste : on retire les titres de département et on réordonne.
		var flat = document.createElement('div');
		flat.className = 'abf-dep-group';
		var ul = document.createElement('ul');
		ul.className = 'abf-dep-stores';
		withDist.forEach(function (item) {
			if (item.d !== Infinity) { ul.appendChild(item.li); }
		});
		flat.appendChild(ul);
		listEl.innerHTML = '';
		listEl.appendChild(flat);
	}

	/* ------------------------------------------------------------------ */
	/* Utilitaires                                                         */
	/* ------------------------------------------------------------------ */

	function haversine(lat1, lon1, lat2, lon2) {
		var R = 6371;
		var dLat = (lat2 - lat1) * Math.PI / 180;
		var dLon = (lon2 - lon1) * Math.PI / 180;
		var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
			Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
			Math.sin(dLon / 2) * Math.sin(dLon / 2);
		return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	}

	function normalize(str) {
		return str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
	}

	function telHref(num) {
		var d = (num || '').replace(/\D+/g, '');
		if (d.charAt(0) === '0') { d = '33' + d.slice(1); }
		return '+' + d;
	}

	function esc(str) {
		var div = document.createElement('div');
		div.textContent = str == null ? '' : str;
		return div.innerHTML;
	}

	function debounce(fn, wait) {
		var t;
		return function () {
			var ctx = this, args = arguments;
			clearTimeout(t);
			t = setTimeout(function () { fn.apply(ctx, args); }, wait);
		};
	}
})();
