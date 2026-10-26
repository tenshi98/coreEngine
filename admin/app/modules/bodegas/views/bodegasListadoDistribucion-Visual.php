<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

// Datos de la distribucion para la escena 3D
$vis3dData = json_encode($data['arrZonas'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<div class="pt-2">

    <?php
    // Leyenda de estados
    if(is_array($data['arrEstado'])&&!empty($data['arrEstado'])){ ?>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach($data['arrEstado'] as $estado){ ?>
                <span class="badge <?php echo $estado['Color']; ?>"><?php echo $estado['Nombre']; ?></span>
            <?php } ?>
        </div>
    <?php } ?>

    <?php
    // Verifico si hay datos
    if(is_array($data['arrZonas'])&&!empty($data['arrZonas'])){ ?>

        <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="vis3dReset()"><i class="bi bi-arrow-counterclockwise"></i> Reiniciar vista</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="vis3dBtnRotate" onclick="vis3dToggleRotate()"><i class="bi bi-arrow-repeat"></i> Rotación: off</button>
            <div class="btn-group" role="group" aria-label="Zoom">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="vis3dZoom(0.8)" title="Acercar"><i class="bi bi-zoom-in"></i></button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="vis3dZoom(1.25)" title="Alejar"><i class="bi bi-zoom-out"></i></button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="vis3dBtnWalls" onclick="vis3dToggleWalls()"><i class="bi bi-building"></i> Paredes: on</button>
            <span class="ms-auto align-self-center"><small class="text-muted">Arrastrar: rotar &middot; Rueda/botones: zoom &middot; Clic en posición: detalle</small></span>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
            <label class="mb-0 small text-muted" for="vis3dRackSelect"><i class="bi bi-list-nested"></i> Enfocar rack:</label>
            <select id="vis3dRackSelect" class="form-select form-select-sm w-auto" onchange="vis3dFocusRack(this.value)">
                <option value="">— Sin enfoque —</option>
            </select>
        </div>

        <div id="vis3d-wrap" style="position:relative;">
            <div id="vis3d-canvas" style="width:100%;height:560px;background:#1b1e23;border-radius:.5rem;overflow:hidden;"></div>
            <div id="vis3d-tip" style="display:none;position:absolute;pointer-events:none;background:rgba(0,0,0,.85);color:#fff;padding:4px 8px;border-radius:4px;font-size:.78rem;z-index:5;white-space:nowrap;"></div>
        </div>

    <?php }else{ ?>
        <div class="empty-state text-center py-4">
            <i class="bi bi-box-seam" style="font-size: 2.5rem;"></i>
            <h5 class="mt-2">No hay distribución para mostrar en 3D</h5>
            <p class="text-muted mb-0">Cree zonas, pasillos y racks desde la pestaña «Distribución» para generar la vista isométrica.</p>
        </div>
    <?php } ?>

</div>

<?php
// Solo se inicia la escena si hay datos
if(is_array($data['arrZonas'])&&!empty($data['arrZonas'])){ ?>
<script>
    (function () {
        // three.js se carga desde la pagina contenedora (distLoadVisual); si falta, se avisa
        if (typeof THREE === 'undefined') {
            document.getElementById('vis3d-canvas').innerHTML =
                '<div class="text-center text-muted py-5">No se pudo cargar three.js. Reintente abrir la pestaña.</div>';
            return;
        }

        /*********************************************************************/
        /*                         ESTADO GLOBAL                             */
        /*********************************************************************/
        // Datos de la distribucion (zonas -> pasillos -> racks -> niveles -> posiciones)
        const VIS3D_DATA = <?php echo $vis3dData; ?>;

        // Mapa de colores bootstrap -> hex (los estados en BD se guardan como clases)
        const VIS3D_COLORS = {
            'bg-primary': '#0d6efd', 'bg-secondary': '#6c757d', 'bg-success': '#198754',
            'bg-danger': '#dc3545', 'bg-warning': '#ffc107', 'bg-info': '#0dcaf0',
            'bg-dark': '#212529', 'bg-light': '#f8f9fa', 'bg-body': '#ffffff', 'bg-white': '#ffffff'
        };
        function vis3dColor(cls, fallback) { return VIS3D_COLORS[cls] || fallback || '#868e96'; }

        // Constantes de layout
        const CELL = 1.0;          // ancho de una posicion
        const LEVEL_H = 0.9;       // alto de un nivel
        const RACK_DEPTH = 1.6;    // profundidad del rack
        const RACK_GAP = 1.2;      // separacion entre racks de un pasillo
        const PASILLO_GAP = 3.0;   // pasillo de circulacion entre bandas
        const CORREDOR_DOBLE = 0; // sin corredor: los racks del par quedan contiguos (pegados)
        const ZONA_GAP = 5.0;      // separacion entre zonas
        const LABEL_H = 0.55;      // altura extra para etiquetas

        /*********************************************************************/
        /*                         RENDERER / ESCENA                         */
        /*********************************************************************/
        const cont  = document.getElementById('vis3d-canvas');
        const tip   = document.getElementById('vis3d-tip');
        const ancho = cont.clientWidth  || 800;
        const alto  = cont.clientHeight || 560;

        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setSize(ancho, alto);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        cont.appendChild(renderer.domElement);

        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x1b1e23);

        const camera = new THREE.PerspectiveCamera(45, ancho / alto, 0.1, 1000);
        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.06;
        controls.maxPolarAngle = Math.PI / 2.05;
        controls.autoRotate = false;
        controls.autoRotateSpeed = 0.6;

        // Luces (maqueta industrial: hemisferica suave + sol con sombras + relleno frio)
        scene.add(new THREE.HemisphereLight(0xdfe8f2, 0x55524c, 0.65));
        scene.add(new THREE.AmbientLight(0xffffff, 0.18));
        const dirLight = new THREE.DirectionalLight(0xfff4e0, 1.0);
        dirLight.position.set(30, 50, 20);
        dirLight.castShadow = true;
        dirLight.shadow.mapSize.set(2048, 2048);
        dirLight.shadow.bias = -0.0006;
        scene.add(dirLight);
        const fillLight = new THREE.DirectionalLight(0xcfe0ff, 0.35);
        fillLight.position.set(-20, 30, -10);
        scene.add(fillLight);

        // Colores de la estructura de rack (estilo industrial)
        const RACK_BLUE   = 0x24539b;   // montantes / uprights
        const RACK_ORANGE = 0xe07b12;   // vigas / beams
        const CONCRETE    = 0x8f8f8a;   // losa de hormigon
        const WALL_GRAY   = 0xd7d7d2;   // paredes cortadas
        const SAFETY_YEL  = 0xf5c518;   // lineas de pasillo
        const DOCK_GRAY   = 0x7a7a75;   // anden de carga

        // Compartidos (performance)
        const posGeometry      = new THREE.BoxGeometry(CELL * 0.8, LEVEL_H * 0.72, RACK_DEPTH * 0.5);
        const baseGeometry     = new THREE.BoxGeometry(1, 0.08, 1);
        const uprightGeometry  = new THREE.BoxGeometry(0.14, 1, RACK_DEPTH);   // Y se escala por alto de rack
        const beamGeometry     = new THREE.BoxGeometry(1, 0.1, 0.14);             // X se escala por ancho de rack
        const stripGeometry    = new THREE.PlaneGeometry(1, 1);                  // lineas de suelo
        const lampWireGeo      = new THREE.CylinderGeometry(0.02, 0.02, 1, 6);
        const lampHeadGeo      = new THREE.ConeGeometry(0.35, 0.4, 12, 1, true);
        const lampBulbGeo      = new THREE.SphereGeometry(0.14, 8, 8);
        const ceilBeamGeo      = new THREE.BoxGeometry(1, 0.15, 0.15);             // X se escala por ancho de escena
        const sharedGeometries = [posGeometry, baseGeometry, uprightGeometry, beamGeometry,
                                  stripGeometry, lampWireGeo, lampHeadGeo, lampBulbGeo, ceilBeamGeo];
        const materialCache = {};
        function getMaterial(colorHex) {
            if (!materialCache[colorHex]) {
                materialCache[colorHex] = new THREE.MeshLambertMaterial({ color: colorHex });
            }
            return materialCache[colorHex];
        }
        // Materiales PROPIOS por rack: permiten atenuar (translucidez) un rack
        // sin afectar a los demas (los materiales compartidos se global).
        const rackMaterialCache = {};
        function getRackMaterial(rackKey, colorHex) {
            const k = rackKey + '|' + colorHex;
            if (!rackMaterialCache[k]) {
                rackMaterialCache[k] = new THREE.MeshLambertMaterial({ color: colorHex });
            }
            return rackMaterialCache[k];
        }
        // Registro de racks para el listado / enfoque
        const rackEntries = [];
        let rackSeq = 0;
        // Franja de pintura de seguridad sobre el suelo (plano rotado, escala en X/Z)
        function addFloorStrip(group, cx, cz, w, d, colorHex, y) {
            const m = new THREE.Mesh(stripGeometry, getMaterial(colorHex));
            m.rotation.x = -Math.PI / 2;
            m.scale.set(w, d, 1);
            m.position.set(cx, (y === undefined ? 0.02 : y), cz);
            group.add(m);
            return m;
        }

        // Etiqueta de texto (sprite sobre la geometria)
        function makeLabel(text, fontPx, colorCss) {
            const c = document.createElement('canvas');
            const ctx = c.getContext('2d');
            const font = 'bold ' + fontPx + 'px system-ui, sans-serif';
            ctx.font = font;
            const w = Math.ceil(ctx.measureText(text).width) + 18;
            const h = fontPx + 14;
            c.width = w; c.height = h;
            const ctx2 = c.getContext('2d');
            ctx2.font = font;
            ctx2.fillStyle = 'rgba(0,0,0,0.6)';
            ctx2.beginPath();
            if (ctx2.roundRect) { ctx2.roundRect(0, 0, w, h, 6); ctx2.fill(); }
            else { ctx2.fillRect(0, 0, w, h); }
            ctx2.fillStyle = colorCss || '#ffffff';
            ctx2.textBaseline = 'middle';
            ctx2.textAlign = 'center';
            ctx2.fillText(text, w / 2, h / 2 + 1);
            const tex = new THREE.CanvasTexture(c);
            const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, transparent: true, depthTest: false }));
            const scale = 0.014;
            sp.scale.set(w * scale, h * scale, 1);
            return sp;
        }

        /*********************************************************************/
        /*                         CONSTRUCCION                              */
        /*********************************************************************/
        // Mallas de posiciones (para raycaster)
        const positionMeshes = [];
        let cursorX = 0;
        let maxY = 1;

        // Grupo del "mundo" (solo zonas/racks); la rejilla se agrega despues
        // para que no influya en el encuadre de la camara
        const mundo = new THREE.Group();
        scene.add(mundo);

        /*********************************************************************/
        /*              CONSTRUCCION DE UN RACK (malla completa)             */
        /*********************************************************************/
        function buildRack(rack) {
            const rn = Math.max(1, rack.Niveles | 0);
            const rp = Math.max(1, rack.PosicionesPorNivel | 0);
            const rackW = rp * CELL;   // ancho util del rack (entre montantes)
            const rackH = rn * LEVEL_H;
            const rackGroup = new THREE.Group();
            const rackKey = 'rk' + (rackSeq++);   // identificador unico para enfoque

            // Palets / posiciones coloreadas por estado, apoyados sobre las vigas
            rack.niveles.forEach(function (nivel) {
                const n = nivel.Nivel | 0;
                nivel.posiciones.forEach(function (pos) {
                    const mesh = new THREE.Mesh(posGeometry, getRackMaterial(rackKey, vis3dColor(pos.Color, '#868e96')));
                    const px = ((pos.Numero | 0) - (rp + 1) / 2) * CELL;
                    const py = (n - 1) * LEVEL_H + 0.16 + (LEVEL_H * 0.72) / 2;
                    mesh.position.set(px, py, 0);
                    mesh.castShadow = true;
                    mesh.receiveShadow = true;
                    mesh.userData = { idEnc: pos.idEnc, codigo: pos.Codigo, estado: pos.Estado };
                    rackGroup.add(mesh);
                    positionMeshes.push(mesh);
                });
            });

            // Montantes azules (frames verticales) en cada bahia
            for (let k = 0; k <= rp; k++) {
                const u = new THREE.Mesh(uprightGeometry, getRackMaterial(rackKey, RACK_BLUE));
                u.scale.y = rackH;
                u.position.set(-rackW / 2 + k * CELL, rackH / 2, 0);
                u.castShadow = true;
                u.receiveShadow = true;
                rackGroup.add(u);
            }

            // Vigas naranjas por nivel (frente y respaldo)
            for (let n = 1; n <= rn; n++) {
                const by = (n - 1) * LEVEL_H + 0.1;
                [-1, 1].forEach(function (sz) {
                    const b = new THREE.Mesh(beamGeometry, getRackMaterial(rackKey, RACK_ORANGE));
                    b.scale.x = rackW;
                    b.position.set(0, by, sz * (RACK_DEPTH / 2 - 0.07));
                    b.castShadow = true;
                    rackGroup.add(b);
                });
            }

            // Zocalo / base del rack
            const base = new THREE.Mesh(baseGeometry, getRackMaterial(rackKey, 0x3a3f45));
            base.scale.set(rackW + 0.2, 1, RACK_DEPTH + 0.2);
            base.position.set(0, 0.04, 0);
            base.receiveShadow = true;
            rackGroup.add(base);

            // Etiqueta del rack
            const rackLabel = makeLabel(rack.Rack, 22, '#adb5bd');
            rackLabel.position.set(0, rackH + LABEL_H, 0);
            rackGroup.add(rackLabel);

            maxY = Math.max(maxY, rackH + LABEL_H * 2);

            // Se colectan los materiales propios del rack (para el enfoque)
            const rackMats = [];
            rackGroup.traverse(function (o) {
                if (o.isMesh && o.material && rackMats.indexOf(o.material) === -1) { rackMats.push(o.material); }
            });
            return { group: rackGroup, key: rackKey, label: rackLabel, materials: rackMats, w: rackW, h: rackH };
        }

        // Se registra el rack en el listado global de enfoque
        function pushRackEntry(built, zona, pasillo, rack) {
            rackEntries.push({
                key: built.key,
                name: (zona.Zona || '') + ' · ' + (pasillo.Pasillo || '') + ' · ' + (rack.Rack || ''),
                group: built.group,
                materials: built.materials,
                label: built.label
            });
        }

        VIS3D_DATA.forEach(function (zona) {
            const zonaGroup = new THREE.Group();
            let bandZ = 0;                 // cursor de bandas (pasillos) en Z
            let zonaWidth = 0;             // ancho maximo de la zona en X

            zona.pasillos.forEach(function (pasillo) {
                let rackX = 0;             // cursor de racks en X dentro de la banda
                let bandDepth = RACK_DEPTH;
                const rackById = {};

                pasillo.racks.forEach(function (r) { rackById[r.idRack] = r; });
                const visited = {};

                for (let ri = 0; ri < pasillo.racks.length; ri++) {
                    const rack = pasillo.racks[ri];
                    if (visited[rack.idRack]) { continue; }
                    visited[rack.idRack] = true;

                    // Rack gemelo (pasillo doble): presente y aun sin colocar
                    let partner = null;
                    const idGem = rack.idRackEnfrentado;
                    if (idGem !== null && rackById[idGem] && !visited[idGem]) {
                        partner = rackById[idGem];
                        visited[idGem] = true;
                    }

                    const A = buildRack(rack);

                    if (partner) {
                        const B = buildRack(partner);

                        if (pasillo.Orientacion === 'V') {
                            // Bandas verticales: ambos inician en la misma Z,
                            // separados en X por el corredor doble, enfrentados.
                            A.group.rotation.y = Math.PI / 2;
                            B.group.rotation.y = Math.PI / 2 + Math.PI;
                            A.group.position.set(rackX + RACK_DEPTH / 2, 0, bandZ + A.w / 2);
                            B.group.position.set(rackX + RACK_DEPTH + CORREDOR_DOBLE + RACK_DEPTH / 2, 0, bandZ + B.w / 2);
                            rackX += 2 * RACK_DEPTH + CORREDOR_DOBLE + RACK_GAP;
                            bandDepth = Math.max(bandDepth, A.w, B.w);
                        } else {
                            // Bandas horizontales: uno tras otro en Z,
                            // separados por el corredor doble, enfrentados.
                            B.group.rotation.y = Math.PI;
                            A.group.position.set(rackX + A.w / 2, 0, bandZ + RACK_DEPTH / 2);
                            B.group.position.set(rackX + B.w / 2, 0, bandZ + RACK_DEPTH + CORREDOR_DOBLE + RACK_DEPTH / 2);
                            rackX += Math.max(A.w, B.w) + RACK_GAP;
                            bandDepth = Math.max(bandDepth, 2 * RACK_DEPTH + CORREDOR_DOBLE);
                        }

                        zonaGroup.add(A.group);
                        zonaGroup.add(B.group);
                        pushRackEntry(A, zona, pasillo, rack);
                        pushRackEntry(B, zona, pasillo, partner);
                    } else if (pasillo.Orientacion === 'V') {
                        // Rack vertical simple (largo en Z)
                        A.group.rotation.y = Math.PI / 2;
                        A.group.position.set(rackX + RACK_DEPTH / 2, 0, bandZ + A.w / 2);
                        rackX += RACK_DEPTH + RACK_GAP;
                        bandDepth = Math.max(bandDepth, A.w);
                        zonaGroup.add(A.group);
                        pushRackEntry(A, zona, pasillo, rack);
                    } else {
                        // Rack horizontal simple (largo en X)
                        A.group.position.set(rackX + A.w / 2, 0, bandZ + RACK_DEPTH / 2);
                        rackX += A.w + RACK_GAP;
                        bandDepth = Math.max(bandDepth, RACK_DEPTH);
                        zonaGroup.add(A.group);
                        pushRackEntry(A, zona, pasillo, rack);
                    }
                }

                // Lineas de seguridad delimitando la banda (pintura de anden)
                if (rackX > 0) {
                    const bandW = rackX - RACK_GAP;   // ancho efectivo sin el ultimo gap
                    addFloorStrip(zonaGroup, bandW / 2, bandZ, bandW, 0.14, SAFETY_YEL);
                    addFloorStrip(zonaGroup, bandW / 2, bandZ + bandDepth, bandW, 0.14, SAFETY_YEL);
                }

                // Etiqueta del pasillo al inicio de la banda
                const pasLabel = makeLabel(pasillo.Pasillo, 26, '#f8f9fa');
                pasLabel.position.set(rackX / 2, maxY + 1.2, bandZ + bandDepth / 2);
                zonaGroup.add(pasLabel);

                // Siguiente banda de pasillo
                bandZ += bandDepth + PASILLO_GAP;
                zonaWidth = Math.max(zonaWidth, rackX);
            });

            // Piso de la zona (tinte del tipo de zona)
            if (zonaWidth > 0 && bandZ > 0) {
                const floorColor = new THREE.Color(vis3dColor(zona.ZonaTipoColor, '#495057'));
                const floor = new THREE.Mesh(
                    new THREE.PlaneGeometry(zonaWidth + 1.5, bandZ - PASILLO_GAP + 1.5),
                    new THREE.MeshBasicMaterial({ color: floorColor, transparent: true, opacity: 0.14, side: THREE.DoubleSide })
                );
                floor.rotation.x = -Math.PI / 2;
                floor.position.set(zonaWidth / 2, 0.01, (bandZ - PASILLO_GAP) / 2);
                zonaGroup.add(floor);
            }

            // Etiqueta de la zona
            const zonaLabel = makeLabel(zona.Zona, 32, '#ffffff');
            zonaLabel.position.set(zonaWidth / 2, maxY + 3.0, (bandZ - PASILLO_GAP) / 2);
            zonaGroup.add(zonaLabel);

            // Se desplaza la zona y se avanza el cursor global
            zonaGroup.position.set(cursorX, 0, 0);
            mundo.add(zonaGroup);
            cursorX += (zonaWidth || 1) + ZONA_GAP;
        });

        /*********************************************************************/
        /*                         CAMARA / VISTA                            */
        /*********************************************************************/
        // Encuadre calculado SOLO sobre las zonas (mundo), sin la rejilla
        const escenaBox = new THREE.Box3().setFromObject(mundo);
        if (escenaBox.isEmpty()) {
            escenaBox.set(new THREE.Vector3(-5, 0, -5), new THREE.Vector3(15, 8, 15));
        }
        const centro = escenaBox.getCenter(new THREE.Vector3());
        const tam = escenaBox.getSize(new THREE.Vector3());
        const dist = Math.max(tam.x, tam.z, 12) * 1.5;

        /*********************************************************************/
        /*              ENTORNO INDUSTRIAL (maqueta de bodega)               */
        /*********************************************************************/
        // Losa de hormigon (base de la maqueta), superiores a y=0
        const slab = new THREE.Mesh(
            new THREE.BoxGeometry(tam.x + 10, 0.4, tam.z + 10),
            getMaterial(CONCRETE)
        );
        slab.position.set(centro.x, -0.2, centro.z);   // cara superior en y=0
        slab.receiveShadow = true;
        scene.add(slab);

        // Paredes cortadas (tipo diorama): fondo y laterales; el frente queda abierto
        const wallH = Math.max(5, maxY * 0.85);
        const wt = 0.25;
        const wallMat = getMaterial(WALL_GRAY);
        const wallMeshes = [];
        function addWall(w, d, x, z) {
            const m = new THREE.Mesh(new THREE.BoxGeometry(w, wallH, d), wallMat);
            m.position.set(x, wallH / 2, z);
            m.castShadow = true;
            m.receiveShadow = true;
            scene.add(m);
            wallMeshes.push(m);
        }
        addWall(tam.x + wt, wt, centro.x, escenaBox.max.z);                 // fondo (+Z)
        addWall(wt, tam.z, escenaBox.min.x, centro.z);                      // izquierda (-X)
        addWall(wt, tam.z, escenaBox.max.x, centro.z);                      // derecha (+X)
        // Esquineras bajas en el frente para enmarcar el corte
        const stub = Math.min(tam.x * 0.12, 3);
        addWall(stub, wt, escenaBox.min.x + stub / 2, escenaBox.min.z);
        addWall(stub, wt, escenaBox.max.x - stub / 2, escenaBox.min.z);

        // Anden de carga en el frente (exterior de la losa)
        const dockW = Math.min(tam.x * 0.4, 10);
        const dock = new THREE.Mesh(new THREE.BoxGeometry(dockW, 0.5, 3), getMaterial(DOCK_GRAY));
        dock.position.set(centro.x, 0.0, escenaBox.min.z - 1.5);
        dock.castShadow = true;
        dock.receiveShadow = true;
        scene.add(dock);
        addFloorStrip(scene, centro.x, escenaBox.min.z - 2.85, dockW, 0.16, SAFETY_YEL, 0.26);

        // Estructura de techo abierta (vigas) + lamparas colgantes industriales
        const CEIL_H = maxY + 3.5;
        const ceilBeam = new THREE.Mesh(ceilBeamGeo, getMaterial(0x3a3f45));
        ceilBeam.scale.x = tam.x + 2;
        ceilBeam.position.set(centro.x, CEIL_H, escenaBox.min.z);
        scene.add(ceilBeam);
        const ceilBeam2 = ceilBeam.clone();
        ceilBeam2.position.z = centro.z;
        scene.add(ceilBeam2);
        const ceilBeam3 = ceilBeam.clone();
        ceilBeam3.position.z = escenaBox.max.z;
        scene.add(ceilBeam3);

        const lampShadeMat = getMaterial(0x2b2f36);
        const lampBulbMat = new THREE.MeshBasicMaterial({ color: 0xfff2c8 });
        const lampY = CEIL_H - 1.2;
        for (let lx = escenaBox.min.x + 3; lx <= escenaBox.max.x - 2; lx += 6) {
            for (let lz = escenaBox.min.z + 3; lz <= escenaBox.max.z - 2; lz += 6) {
                const wire = new THREE.Mesh(lampWireGeo, lampShadeMat);
                wire.scale.y = CEIL_H - lampY;
                wire.position.set(lx, (CEIL_H + lampY) / 2, lz);
                scene.add(wire);
                const head = new THREE.Mesh(lampHeadGeo, lampShadeMat);
                head.rotation.x = Math.PI;   // cono abierto hacia abajo
                head.position.set(lx, lampY, lz);
                head.castShadow = true;
                scene.add(head);
                const bulb = new THREE.Mesh(lampBulbGeo, lampBulbMat);
                bulb.position.set(lx, lampY - 0.15, lz);
                scene.add(bulb);
            }
        }

        // Camara de sombras ajustada a la extension real de la escena
        dirLight.target.position.copy(centro);
        scene.add(dirLight.target);
        dirLight.position.set(centro.x + dist * 0.6, dist * 1.2, centro.z + dist * 0.5);
        const sc = dirLight.shadow.camera;
        const half = Math.max(tam.x, tam.z) * 0.75 + 5;
        sc.left = -half; sc.right = half; sc.top = half; sc.bottom = -half;
        sc.near = 1; sc.far = dist * 3;
        sc.updateProjectionMatrix();

        // Niebla proporcional (no fija) para que nunca tape la escena
        scene.fog = new THREE.Fog(0x1b1e23, dist * 1.6, dist * 3.2);

        function vis3dApplyView() {
            camera.position.set(centro.x + dist * 0.7, dist * 0.9, centro.z + dist * 0.95);
            camera.lookAt(centro);
            controls.target.copy(centro);
            controls.update();
        }
        vis3dApplyView();

        // Poblar el listado de racks (para enfoque)
        const rackSelect = document.getElementById('vis3dRackSelect');
        if (rackSelect) {
            rackEntries.forEach(function (e) {
                const opt = document.createElement('option');
                opt.value = e.key;
                opt.textContent = e.name;
                rackSelect.appendChild(opt);
            });
        }

        // Expone acciones de la barra de herramientas
        window.vis3dReset = function () {
            const sel = document.getElementById('vis3dRackSelect');
            if (sel) { sel.value = ''; }
            applyRackFocus(null);
            vis3dApplyView();
        };
        window.vis3dToggleRotate = function () {
            controls.autoRotate = !controls.autoRotate;
            const btn = document.getElementById('vis3dBtnRotate');
            if (btn) { btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Rotación: ' + (controls.autoRotate ? 'on' : 'off'); }
        };
        // Zoom por botones (ademas de la rueda del raton)
        window.vis3dZoom = function (factor) {
            const offset = camera.position.clone().sub(controls.target);
            let len = offset.length() * factor;
            len = Math.max(3, Math.min(500, len));
            offset.setLength(len);
            camera.position.copy(controls.target).add(offset);
            controls.update();
        };
        // Mostrar / ocultar paredes del diorama
        let wallsVisible = true;
        window.vis3dToggleWalls = function () {
            wallsVisible = !wallsVisible;
            wallMeshes.forEach(function (m) { m.visible = wallsVisible; });
            const btn = document.getElementById('vis3dBtnWalls');
            if (btn) { btn.innerHTML = '<i class="bi bi-building"></i> Paredes: ' + (wallsVisible ? 'on' : 'off'); }
        };
        // Atenuar (translucido) todos los racks salvo el seleccionado
        function applyRackFocus(key) {
            rackEntries.forEach(function (e) {
                const dim = !!key && e.key !== key;
                e.materials.forEach(function (m) {
                    m.transparent = dim;
                    m.opacity = dim ? 0.15 : 1.0;
                    m.needsUpdate = true;
                });
                if (e.label) { e.label.material.opacity = dim ? 0.2 : 1.0; }
            });
        }
        // Enfocar un rack: atenua el resto y centra la camara en el seleccionado
        window.vis3dFocusRack = function (val) {
            applyRackFocus(val || null);
            if (!val) { return; }
            let entry = null;
            for (let i = 0; i < rackEntries.length; i++) {
                if (rackEntries[i].key === val) { entry = rackEntries[i]; break; }
            }
            if (!entry) { return; }
            scene.updateMatrixWorld(true);
            const box = new THREE.Box3().setFromObject(entry.group);
            const center = box.getCenter(new THREE.Vector3());
            const size = box.getSize(new THREE.Vector3());
            const d = Math.max(size.x, size.y, size.z, 3) * 2.2 + 3;
            camera.position.set(center.x + d * 0.7, center.y + d * 0.6, center.z + d * 0.9);
            controls.target.copy(center);
            controls.update();
        };

        /*********************************************************************/
        /*                    INTERACCION (HOVER / CLIC)                     */
        /*********************************************************************/
        const raycaster = new THREE.Raycaster();
        const mouseNDC = new THREE.Vector2();
        let mouseXY = { x: 0, y: 0 };
        let hoverActual = null;

        function onMouseMove(e) {
            const rect = renderer.domElement.getBoundingClientRect();
            mouseXY.x = e.clientX - rect.left;
            mouseXY.y = e.clientY - rect.top;
            mouseNDC.x = (mouseXY.x / rect.width) * 2 - 1;
            mouseNDC.y = -(mouseXY.y / rect.height) * 2 + 1;
        }
        renderer.domElement.addEventListener('mousemove', onMouseMove);

        function onClick() {
            if (!hoverActual) { return; }
            // Reutiliza el modal de posiciones del Resumen
            if (typeof distPosicionView === 'function' && hoverActual.userData.idEnc) {
                distPosicionView(hoverActual.userData.idEnc);
            }
        }
        renderer.domElement.addEventListener('click', onClick);

        /*********************************************************************/
        /*                            ANIMACION                              */
        /*********************************************************************/
        let rafId = 0;
        function animate() {
            rafId = requestAnimationFrame(animate);
            controls.update();

            // Hover: raycast solo cuando hay movimiento reciente
            raycaster.setFromCamera(mouseNDC, camera);
            const hits = raycaster.intersectObjects(positionMeshes, false);
            const top = hits.length ? hits[0].object : null;
            if (top !== hoverActual) {
                if (hoverActual) { hoverActual.scale.set(1, 1, 1); }
                hoverActual = top;
                if (hoverActual) { hoverActual.scale.set(1.12, 1.12, 1.12); }
            }
            if (hoverActual) {
                tip.style.display = 'block';
                tip.style.left = (mouseXY.x + 14) + 'px';
                tip.style.top  = (mouseXY.y + 10) + 'px';
                tip.textContent = hoverActual.userData.codigo + ' — ' + hoverActual.userData.estado;
                renderer.domElement.style.cursor = 'pointer';
            } else {
                tip.style.display = 'none';
                renderer.domElement.style.cursor = 'grab';
            }

            renderer.render(scene, camera);
        }
        animate();

        /*********************************************************************/
        /*                         LIMPIEZA / RESIZE                         */
        /*********************************************************************/
        function onResize() {
            const w = cont.clientWidth, h = cont.clientHeight;
            if (!w || !h) { return; }
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
        }
        window.addEventListener('resize', onResize);

        // Destruccion global: la pagina contenedora la invoca antes de recargar el tab
        window.bodegaVisual3D = {
            destroy: function () {
                cancelAnimationFrame(rafId);
                window.removeEventListener('resize', onResize);
                renderer.domElement.removeEventListener('mousemove', onMouseMove);
                renderer.domElement.removeEventListener('click', onClick);
                renderer.dispose();
                scene.traverse(function (obj) {
                    if (obj.geometry && sharedGeometries.indexOf(obj.geometry) === -1) { obj.geometry.dispose(); }
                    if (obj.material) {
                        const mats = Array.isArray(obj.material) ? obj.material : [obj.material];
                        mats.forEach(function (m) {
                            if (m.map) { m.map.dispose(); }
                            m.dispose();
                        });
                    }
                });
                sharedGeometries.forEach(function (g) { g.dispose(); });
                Object.keys(materialCache).forEach(function (k) { materialCache[k].dispose(); });
                Object.keys(rackMaterialCache).forEach(function (k) { rackMaterialCache[k].dispose(); });
                if (renderer.domElement.parentNode) { renderer.domElement.parentNode.removeChild(renderer.domElement); }
                delete window.bodegaVisual3D;
                delete window.vis3dReset;
                delete window.vis3dToggleRotate;
                delete window.vis3dZoom;
                delete window.vis3dToggleWalls;
                delete window.vis3dFocusRack;
            }
        };
    })();
</script>
<?php } ?>
