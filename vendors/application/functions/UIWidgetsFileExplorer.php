<?php
/*******************************************************************************************************************/
/*                                              Se define la clase                                                 */
/*******************************************************************************************************************/
class UIWidgetsFileExplorer {

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                                 Instancias                                                      */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	//Definiciones
	private $Alertas;

	/******************************************************************************/
	/**
	 * Crea las instancias internas que la clase necesita: el proveedor de alertas de UIWidgetsCommon.
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 * $FileExplorer = new UIWidgetsFileExplorer();
	 * ```
	 */
	public function __construct() {
		$this->Alertas = new UIWidgetsCommon();
	}

	/*******************************************************************************************************************/
	/*                                                                                                                 */
	/*                                              Metodos Publicos                                                   */
	/*                                                                                                                 */
	/*******************************************************************************************************************/
	/******************************************************************************/
	/**
	 * Genera un explorador de archivos con vista de cuadrícula o lista, búsqueda y subida.
	 *
	 * Claves de $Options: 'BASE', 'rootPath' y 'Route' (obligatorias), 'ValidarTipo' ('all'),
	 * 'levelPermission' (4: 1 solo ver, 2 subir, 3 borrar) y 'AccessScope' (ámbito de
	 * autorización; sin él el endpoint deniega y no se pintan los botones de escritura/borrado).
	 * El listado se obtiene en cliente del endpoint core/fileExplorer y se filtra por reglas
	 * de seguridad (nombres, extensiones y carpetas excluidas). Si falta una clave obligatoria
	 * imprime una alerta y detiene la ejecución (exit).
	 *
	 * @param array $Options Arreglo con las opciones del explorador ('BASE', 'rootPath', 'Route', 'ValidarTipo', 'levelPermission', 'AccessScope').
	 *
	 * @return void Imprime el explorador de archivos (HTML + estilos + script) en salida estándar.
	 *
	 * @example
	 * ```php
	 * $FileExplorer = new UIWidgetsFileExplorer();
	 * $Options = [
	 *     'BASE'            => 'https://mi-sitio.cl', // Ruta de la raíz del sitio
	 *     'rootPath'        => '/var/www/uploads',    // Raíz física de archivos
	 *     'Route'           => 'carpeta/subcarpeta',  // Ruta de la carpeta a mostrar
	 *     'ValidarTipo'     => 'word,excel',          // Archivos permitidos a mostrar
	 *     'levelPermission' => 4,                     // Nivel de permiso (1 ver | 2 subir | 3 borrar)
	 * ];
	 * $FileExplorer->widget_fileExplorer($Options); // imprime el explorador de archivos
	 * ```
	 */
	public function widget_fileExplorer($Options){

		/**********************  Validaciones   **********************/
		if ($Options['BASE'] === null || trim((string)$Options['BASE']) === '') {          echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el BASE.');     exit;}
		if ($Options['rootPath'] === null || trim((string)$Options['rootPath']) === '') {  echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el rootPath.'); exit;}
		if ($Options['Route'] === null || trim((string)$Options['Route']) === '') {        echo $this->Alertas->alertPostData(4, 4, 'exclamation-circle', 1, 'No ha ingresado el Route.');    exit;}

		/**********************  Definiciones   **********************/
		$fnc_Codification   = new FunctionsSecurityCodification();
		$BASE               = $Options['BASE'];
		$rootPath           = rtrim($Options['rootPath'],'/').'/';
		$SubRoute           = $Options['Route'];
		$RouteID            = $fnc_Codification->encryptDecrypt('encrypt', $SubRoute);
		$Route              = $RouteID['data'];
		// Opcionales
		$ValidarTipo        = (isset($Options['ValidarTipo'])&&$Options['ValidarTipo']!='')         ? $Options['ValidarTipo']     : 'all';
		$levelPermission    = (isset($Options['levelPermission'])&&$Options['levelPermission']!='') ? $Options['levelPermission'] : 4;

		/******************************************************************************/
		// Ámbito de autorización: identifica el módulo que implementa esta vista para que el endpoint
		// valide la concesión de sesión (ScopeAccess). Se sanea con la misma regla que
		// ScopeAccess::normalizeScope(). El nivel NO viaja en la petición: solo se transporta el ámbito.
		/******************************************************************************/
		$AccessScope        = (isset($Options['AccessScope'])&&$Options['AccessScope']!='') ? $Options['AccessScope'] : '';
		$AccessScope        = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$AccessScope);
		// Sin ámbito válido el endpoint denegaría (fail-closed); aquí se evita además pintar botones inútiles
		$escrituraPermitida = ($AccessScope !== '') && ((int)$levelPermission >= 2);
		$borradoPermitido   = ($AccessScope !== '') && ((int)$levelPermission >= 3);

		// Valores JSON seguros para incrustarlos en literales JS (evitan rotura de string / inyección)
		$SubRouteJS = json_encode((string)$SubRoute, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
		$rootPathJS = json_encode((string)$rootPath, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

		/********************** Si todo esta ok **********************/
		$widget  = '
			<div class="file-explorer">

				<!-- Toolbar -->
				<div class="toolbar p-2 d-flex justify-content-between align-items-center">
					<div class="btn-group">
						<button class="btn btn-sm btn-outline-secondary" onclick="setView(\'grid\')"><i class="bi bi-grid"></i></button>
						<button class="btn btn-sm btn-outline-secondary" onclick="setView(\'list\')"><i class="bi bi-card-list"></i></button>
					</div>';

					if($escrituraPermitida){
						$widget  .= '
						<div class="btn-group">
							<button class="btn btn-sm btn-outline-primary"     onclick="document.getElementById(\'fileInput\').click()"><i class="bi bi-upload"></i> Subir Archivo</button>
							<button class="btn btn-sm btn-outline-secondary"   onclick="createNewFolder()"><i class="bi bi-folder-plus"></i> Crear Carpeta</button>
						</div>
						<input type="file" id="fileInput" style="display:none" onchange="uploadFile(this)">';
					}

					$widget  .= '
					<div class="input-group input-group-sm" style="max-width: 250px;">
						<span class="input-group-text"><i class="bi bi-search"></i></span>
						<input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
					</div>
				</div>

				<!-- Breadcrumb -->
				<div class="p-2 border-bottom">
					<nav><ol class="breadcrumb" id="breadcrumb"></ol></nav>
				</div>

				<!-- Lista de archivos -->
				<div class="table-responsive file-content">
					<div id="gridView" class="file-grid"></div>
					<table id="listView" class="table table-hover bg-white d-none">
						<thead>
							<tr>
								<th scope="col">Nombre</th>
								<th scope="col">Tamaño</th>
								<th scope="col">Fecha</th>';
								if($borradoPermitido){$widget  .= '<th scope="col">Acciones</th>';}
								$widget  .= '
							</tr>
						</thead>
						<tbody id="fileList"></tbody>
					</table>
				</div>

			</div>

			<!-- MODAL PREVIEW -->
			<div class="modal fade" id="previewModal">
				<div class="modal-dialog modal-xl">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="previewTitle"></h5>
							<button type="button" class="btn-close" aria-label="Close" data-modal-close></button>
						</div>
						<div class="modal-body" id="previewBody"></div>
						<div class="modal-footer">
							<div class="d-grid gap-2 d-md-flex justify-content-md-end w-100" id="previewActions"></div>
						</div>
					</div>
				</div>
			</div>

			<style>
				.file-explorer {overflow: hidden;background: #fff;border: 1px solid var(--card-border-color);border-radius: 5px;}
				.file-explorer .toolbar {background: #f1f3f5;border-bottom: 1px solid #dee2e6;}
				.file-explorer .breadcrumb {margin-bottom: 0;}
				.file-explorer .file-content{min-height:400px;}

				.file-explorer .file-grid {display: grid;grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));gap: 15px;}
				.file-explorer .file-card {background: white;border-radius: 10px;padding: 15px;text-align: center;transition: 0.2s;}
				.file-explorer .file-card:hover {background: #e9f2ff;cursor: pointer;}
				.file-explorer .file-icon {font-size: 40px;}
				.file-explorer .file-name {font-size: 13px;word-break: break-word;}
				.file-explorer .view-toggle button {margin-left: 5px;}

				.file-explorer .table thead {background-color: #f8f9fa;}
				.file-explorer .table tbody tr td {cursor: pointer;}
			</style>

			<script>

				let currentPath = "";
				let currentView = "grid";
				let allFiles    = [];

				/**
				 * ===================================================================================
				 * CONFIGURACIÓN DE SEGURIDAD
				 * ===================================================================================
				 */
				const EXCLUDED_NAMES = [
					".htaccess", ".htpasswd", ".env", ".env.local", ".env.production", ".env.dev",
					".gitignore", ".gitattributes", "config.php", "configuration.php", "settings.php",
					"web.config", "composer.json", "composer.lock", "package.json", "package-lock.json",
					"yarn.lock", "Dockerfile", "docker-compose.yml", "phpunit.xml", "README.md",
					"LICENSE", "error_log", "access.log"
				];

				const EXCLUDED_EXTENSIONS = [
					"php", "php3", "php4", "php5", "php7", "php8",
					"phtml", "pht", "phtm", "phar", "phps", "phpt",
					"ini", "env", "conf", "config", "yaml", "yml", "toml",
					"log",
					"sh", "bash", "zsh", "bat", "cmd", "ps1",
					"exe", "bin", "run",
					"sql", "bak", "old", "backup", "dump",
					"cgi", "pl", "py", "rb", "jsp", "asp", "aspx",
					"shtml", "shtm", "xhtml", "inc"
				];

				const EXCLUDED_FOLDERS = [
					".git", ".svn", ".hg",
					"node_modules", "vendor",
					".idea", ".vscode",
					"bin", "etc", "var", "proc", "sys", "dev", "tmp",
					"logs", "log", "cache", "storage",
					"backup", "backups",
					".docker", ".github",
					"tests", "test"
				];

				function sanitizePath(path) {
					return path.replace(/\.\./g, "");
				}

				/**
				 * ===================================================================================
				 * FILTRO DE SEGURIDAD
				 * ===================================================================================
				 */
				/**
				 * Determina si un archivo o carpeta está permitido según reglas de exclusión.
				 *
				 * Reglas aplicadas:
				 * 1. Excluye archivos/carpetas por nombre exacto.
				 * 2. Excluye carpetas completas por nombre.
				 * 3. Excluye archivos según su extensión.
				 *
				 * @param {Object} file - Objeto que representa un archivo o carpeta.
				 * @param {string} file.name - Nombre del archivo o carpeta.
				 * @param {string} file.type - Tipo del elemento ("file" o "folder").
				 *
				 * @returns {boolean}
				 * - true  => El archivo/carpeta está permitido.
				 * - false => El archivo/carpeta está bloqueado según las reglas.
				 *
				 * @example
				 * const file = { name: "config.php", type: "file" };
				 * isAllowed(file); // false (si "php" está en EXCLUDED_EXTENSIONS)
				 */
				function isAllowed(file) {
					// Normaliza el nombre a minúsculas para evitar problemas de comparación
					const name = file.name.toLowerCase();

					/**
					 * 1. Exclusión por nombre exacto
					 * Ej: ".env", "config.php", "thumbs.db"
					 */
					if (EXCLUDED_NAMES.includes(name)) {
						return false;
					}

					/**
					 * 2. Exclusión de carpetas completas
					 * Solo aplica si el tipo es "folder"
					 * Ej: "node_modules", ".git", "vendor"
					 */
					if (file.type === "folder" && EXCLUDED_FOLDERS.includes(name)) {
						return false;
					}

					/**
					 * 3. Exclusión por extensión de archivo
					 * Extrae la extensión usando regex:
					 * - Busca el texto después del último punto
					 * - Ej: "archivo.tar.gz" → "gz"
					 */
					const extMatch = name.match(/\.([^.]+)$/);

					if (extMatch) {
						const ext = extMatch[1];

						/**
						 * Verifica si la extensión está en la lista negra
						 * Ej: ["exe", "bat", "sh", "php"]
						 */
						if (EXCLUDED_EXTENSIONS.includes(ext)) {
							return false;
						}
					}

					/**
					 * Si no cumple ninguna regla de exclusión, el archivo es permitido
					 */
					return true;
				}

				/**
				 * Carga y renderiza la lista de archivos/carpetas desde el servidor
				 * según una ruta dada, aplicando sanitización, seguridad y ordenamiento.
				 *
				 * Flujo:
				 * 1. Sanitiza la ruta recibida.
				 * 2. Transforma la ruta a un formato compatible con el backend.
				 * 3. Realiza petición HTTP para obtener los archivos.
				 * 4. Aplica filtros de seguridad (isAllowed).
				 * 5. Ordena: carpetas primero, luego archivos.
				 * 6. Actualiza estado global y renderiza UI.
				 *
				 * @async
				 * @function loadFiles
				 *
				 * @param {string} [path=""] - Ruta actual a cargar.
				 *
				 * @returns {Promise<void>}
				 *
				 * @example
				 * await loadFiles();          // Carga raíz
				 * await loadFiles("/images"); // Carga carpeta específica
				 */
				async function loadFiles(path = "") {

					/**
					 * ===============================
					 * 1. SANITIZACIÓN DE RUTA
					 * ===============================
					 * Evita rutas inválidas o inseguras (ej: "../", "//", etc.)
					 */
					path = sanitizePath(path);

					let finalPath = "";

					/**
					 * ===============================
					 * 2. TRANSFORMACIÓN DE RUTA
					 * ===============================
					 * El endpoint de listado es
					 *   GET /core/fileExplorer/updateList/@route/@tipos/@path/@scope
					 * donde @path es un token de Fat-Free que solo admite UN segmento
					 * (no puede contener "/"). Por eso la ruta se transporta codificada:
					 * - Se elimina el "/" inicial
					 * - Se reemplazan "/" por "ntn" (token de barra)
					 *
					 * Ej:
					 * "/folder/sub" → "folderntnsub"
					 *
					 * Caso especial:
					 * - Si no hay path, se envía "asdqwe" (token de raíz), porque un
					 *   segmento vacío no es una URL válida.
					 *
					 * Los tokens ("ntn"/"asdqwe") están centralizados en
					 * FilePolicy::EXPLORER_SLASH_TOKEN / EXPLORER_ROOT_TOKEN.
					 */
					if (!path) {
						finalPath = "asdqwe"; // Token de raíz (ver FilePolicy::EXPLORER_ROOT_TOKEN)
					} else {
						let cleanedPath = path.replace(/^\//, "");
						cleanedPath = cleanedPath.replace(/\//g, "ntn"); // Token de barra
						finalPath = cleanedPath;
					}

					/**
					 * ===============================
					 * 3. PETICIÓN AL BACKEND
					 * ===============================
					 * Obtiene listado de archivos/carpetas en formato JSON
					 */
					const res = await fetch(`'.$BASE.'/core/fileExplorer/updateList/'.$Route.'/'.$ValidarTipo.'/${finalPath}/'.$AccessScope.'`);
					let files = await res.json();

					// Guarda de autorización: el endpoint responde 403 con un objeto de error cuando
					// el usuario no tiene concesión de nivel "ver" para este ámbito. Sin esta guarda
					// el .filter() de abajo iteraría un objeto y rompería el render del explorador.
					if (!Array.isArray(files)) {
						throw new Error(files && files.message ? files.message : "Sin acceso al explorador de archivos");
					}

					/**
					 * Guarda la ruta actual (estado global)
					 */
					currentPath = path;

					/**
					 * ===============================
					 * 4. FILTRO DE SEGURIDAD
					 * ===============================
					 * Elimina archivos/carpetas no permitidos
					 * según reglas definidas en isAllowed()
					 */
					files = files.filter(isAllowed);

					/**
					 * ===============================
					 * 5. ORDENAMIENTO
					 * ===============================
					 * - Carpetas primero
					 * - Luego archivos
					 * - Orden alfabético case-insensitive
					 */
					const folders = files
						.filter(f => f.type === "folder")
						.sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: \'base\' }));

					const others = files
						.filter(f => f.type !== "folder")
						.sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: \'base\' }));

					const sortedFiles = [...folders, ...others];

					/**
					 * ===============================
					 * 6. ACTUALIZACIÓN DE ESTADO UI
					 * ===============================
					 */

					// Dataset completo (usado por buscador)
					allFiles = sortedFiles;

					// Reset del input de búsqueda
					document.getElementById("searchInput").value = "";

					/**
					 * ===============================
					 * 7. RENDERIZADO
					 * ===============================
					 */

					// Renderiza lista de archivos
					render(sortedFiles);

					// Renderiza navegación tipo breadcrumb
					renderBreadcrumb();
				}

				/**
				 * Renderiza los archivos en las distintas vistas disponibles.
				 *
				 * Esta función actúa como orquestador de renderizado, delegando
				 * la visualización a diferentes componentes de UI:
				 *
				 * - Vista tipo grid (tarjetas / iconos)
				 * - Vista tipo lista (tabla / detalles)
				 *
				 * Ambas vistas se actualizan con el mismo dataset para mantener
				 * consistencia entre modos de visualización.
				 *
				 * @function render
				 *
				 * @param {Array<Object>} files - Lista de archivos/carpetas a renderizar.
				 * @param {string} files[].name - Nombre del archivo o carpeta.
				 * @param {string} files[].type - Tipo del elemento ("file" o "folder").
				 * @param {number} [files[].size] - Tamaño del archivo (opcional).
				 *
				 * @returns {void}
				 *
				 * @example
				 * render(files); // Actualiza grid y lista con los mismos datos
				 */
				function render(files) {

					/**
					 * ===============================
					 * 1. RENDER VISTA GRID
					 * ===============================
					 * Representación visual tipo explorador moderno
					 * (iconos grandes, tarjetas, preview, etc.)
					 */
					renderGrid(files);

					/**
					 * ===============================
					 * 2. RENDER VISTA LISTA
					 * ===============================
					 * Representación tipo tabla
					 * (nombre, tamaño, fecha, etc.)
					 */
					renderList(files);
				}

				/**
				 * Renderiza los archivos/carpetas en formato de grilla (grid view).
				 *
				 * Cada elemento se representa como una tarjeta visual ("file-card")
				 * que incluye:
				 * - Ícono del archivo/carpeta
				 * - Nombre del archivo
				 *
				 * Además, permite interacción mediante doble clic para abrir el elemento.
				 *
				 * @function renderGrid
				 *
				 * @param {Array<Object>} files - Lista de archivos/carpetas a renderizar.
				 * @param {string} files[].name - Nombre del archivo o carpeta.
				 * @param {string} files[].type - Tipo del elemento ("file" o "folder").
				 *
				 * @returns {void}
				 *
				 * @example
				 * renderGrid(files); // Renderiza tarjetas visuales en el contenedor grid
				 */
				function renderGrid(files) {

					/**
					 * ===============================
					 * 1. OBTENER CONTENEDOR
					 * ===============================
					 * Elemento HTML donde se renderiza la grilla
					 */
					const grid = document.getElementById("gridView");

					/**
					 * Limpia contenido previo para evitar duplicados
					 */
					grid.innerHTML = "";

					/**
					 * ===============================
					 * 2. ITERACIÓN DE ARCHIVOS
					 * ===============================
					 * Se crea una tarjeta por cada archivo/carpeta
					 */
					files.forEach(file => {

						/**
						 * Contenedor principal de la tarjeta
						 */
						const div = document.createElement("div");
						div.className = "file-card";

						/**
						 * Evento de doble clic
						 * - Abre carpeta o archivo
						 */
						div.ondblclick = () => openItem(file);

						/**
						 * ===============================
						 * 3. CONTENIDO HTML
						 * ===============================
						 * - Ícono dinámico según tipo de archivo
						 * - Nombre del archivo
						 */
						div.innerHTML = `
							<div class="file-icon">${getIcon(file)}</div>
							<div class="file-name">${file.name}</div>
						`;

						/**
						 * ===============================
						 * 4. INSERCIÓN EN EL DOM
						 * ===============================
						 */
						grid.appendChild(div);
					});
				}


				/**
				 * Renderiza los archivos/carpetas en formato de lista (tabla).
				 *
				 * Cada elemento se representa como una fila (<tr>) con:
				 * - Ícono + nombre
				 * - Tamaño (si aplica)
				 * - Fecha
				 * - Acciones (ej: eliminar, según permisos)
				 *
				 * Permite interacción mediante doble clic para abrir el elemento.
				 *
				 * ⚠️ IMPORTANTE:
				 * Este código mezcla JavaScript con PHP embebido para control de permisos,
				 * lo que implica que parte del HTML se construye en el servidor.
				 *
				 * @function renderList
				 *
				 * @param {Array<Object>} files - Lista de archivos/carpetas a renderizar.
				 * @param {string} files[].name - Nombre del archivo o carpeta.
				 * @param {string} files[].type - Tipo ("file" o "folder").
				 * @param {number} [files[].size] - Tamaño en bytes (opcional).
				 * @param {string} [files[].date] - Fecha de modificación.
				 *
				 * @returns {void}
				 *
				 * @example
				 * renderList(files); // Renderiza tabla de archivos
				 */
				function renderList(files) {

					/**
					 * ===============================
					 * 1. OBTENER CONTENEDOR
					 * ===============================
					 * <tbody> donde se insertan las filas
					 */
					const list = document.getElementById("fileList");

					/**
					 * Limpia contenido previo
					 */
					list.innerHTML = "";

					/**
					 * ===============================
					 * 2. ITERACIÓN DE ARCHIVOS
					 * ===============================
					 */
					files.forEach(file => {

						/**
						 * Fila de la tabla
						 */
						const row = document.createElement("tr");

						/**
						 * Evento de doble clic
						 * - Abre archivo o carpeta
						 */
						row.ondblclick = () => openItem(file);

						/**
						 * ===============================
						 * 3. CONTENIDO HTML
						 * ===============================
						 * ⚠️ Incluye PHP para control de permisos
						 */
						row.innerHTML = `
							<td>${getIcon(file)} ${file.name}</td>
							<td>${file.size ? formatSize(file.size) : "-"}</td>
							<td>${file.date}</td>';
							if($borradoPermitido){
								$widget .= '
								<td>
									<button class="btn btn-sm btn-outline-danger"
										onclick="${file.type === "folder"
											? `deleteFolder(\'${file.name}\')`
											: `deleteFile(\'${file.name}\')`}">
										<i class="bi bi-trash"></i> Borrar
									</button>
								</td>';
								}
							$widget .= '
						`;

						/**
						 * ===============================
						 * 4. INSERCIÓN EN EL DOM
						 * ===============================
						 */
						list.appendChild(row);
					});
				}

				/**
				 * Obtiene el ícono correspondiente a un archivo o carpeta
				 * basado en su tipo o extensión.
				 *
				 * Utiliza Bootstrap Icons para representar visualmente
				 * diferentes tipos de archivos (imágenes, documentos, código, etc.).
				 *
				 * @function getIcon
				 *
				 * @param {Object} file - Objeto que representa un archivo o carpeta.
				 * @param {string} file.name - Nombre del archivo.
				 * @param {string} file.type - Tipo del elemento ("file" o "folder").
				 *
				 * @returns {string} HTML string con el ícono correspondiente.
				 *
				 * @example
				 * getIcon({ name: "foto.jpg", type: "file" });
				 * // <i class="bi bi-file-image text-info"></i>
				 *
				 * getIcon({ name: "documento.pdf", type: "file" });
				 * // <i class="bi bi-file-pdf text-danger"></i>
				 *
				 * getIcon({ name: "carpeta", type: "folder" });
				 * // <i class="bi bi-folder-fill text-warning"></i>
				 */
				function getIcon(file) {

					/**
					 * ===============================
					 * 1. CARPETAS
					 * ===============================
					 * Se prioriza la detección de carpetas
					 */
					if (file.type === "folder") {
						return "<i class=\'bi bi-folder-fill text-warning\'></i>";
					}

					/**
					 * Normaliza el nombre para evitar problemas de case-sensitive
					 */
					const name = file.name.toLowerCase();

					/**
					 * ===============================
					 * 2. OBTENER EXTENSIÓN
					 * ===============================
					 * Extrae la extensión del archivo usando regex:
					 * - Captura lo que está después del último "."
					 * - Ej: "archivo.tar.gz" → "gz"
					 */
					const extMatch = name.match(/\.([^.]+)$/);
					const ext = extMatch ? extMatch[1] : "";

					/**
					 * ===============================
					 * 3. MAPEO DE ICONOS
					 * ===============================
					 * Relación entre extensiones y clases de Bootstrap Icons
					 */
					const iconMap = {

						/** Imágenes */
						jpg: "bi-file-image text-info",
						jpeg: "bi-file-image text-info",
						png: "bi-file-image text-info",
						gif: "bi-file-image text-info",
						webp: "bi-file-image text-info",
						svg: "bi-file-image text-info",
						bmp: "bi-file-image text-info",

						/** PDF */
						pdf: "bi-file-pdf text-danger",

						/** Word */
						doc: "bi-file-word text-primary",
						docx: "bi-file-word text-primary",
						rtf: "bi-file-word text-primary",

						/** Excel */
						xls: "bi-file-excel text-success",
						xlsx: "bi-file-excel text-success",
						csv: "bi-file-excel text-success",

						/** PowerPoint */
						ppt: "bi-file-ppt text-warning",
						pptx: "bi-file-ppt text-warning",

						/** Código */
						js: "bi-filetype-js text-warning",
						html: "bi-filetype-html text-danger",
						css: "bi-filetype-css text-primary",
						json: "bi-filetype-json text-info",
						xml: "bi-filetype-xml text-secondary",
						py: "bi-filetype-py text-warning",
						java: "bi-filetype-java text-danger",
						c: "bi-filetype-c text-primary",
						cpp: "bi-filetype-cpp text-primary",
						cs: "bi-filetype-cs text-success",
						php: "bi-filetype-php text-indigo",

						/** Texto */
						txt: "bi-file-text text-secondary",
						md: "bi-file-text text-secondary",

						/** Comprimidos */
						zip: "bi-file-zip text-warning",
						rar: "bi-file-zip text-warning",
						// 7z: "bi-file-zip text-warning", // Comentado por soporte limitado en Bootstrap Icons
						tar: "bi-file-zip text-warning",
						gz: "bi-file-zip text-warning",

						/** Video */
						mp4: "bi-file-play text-danger",
						avi: "bi-file-play text-danger",
						mkv: "bi-file-play text-danger",
						mov: "bi-file-play text-danger",

						/** Audio */
						mp3: "bi-file-music text-success",
						wav: "bi-file-music text-success",
						ogg: "bi-file-music text-success",

						/** Binarios / instaladores */
						exe: "bi-file-earmark-binary text-dark",
						apk: "bi-file-earmark-binary text-success"
					};

					/**
					 * ===============================
					 * 4. RESOLUCIÓN DE ÍCONO
					 * ===============================
					 * - Busca en el mapa
					 * - Si no existe, usa ícono genérico
					 */
					const iconClass = iconMap[ext] || "bi-file-earmark text-muted";

					/**
					 * ===============================
					 * 5. RETORNO HTML
					 * ===============================
					 */
					return `<i class="bi ${iconClass}"></i>`;
				}

				/**
				 * Maneja la acción de apertura de un elemento (archivo o carpeta).
				 *
				 * - Si es carpeta: navega a su contenido.
				 * - Si es archivo: abre vista previa.
				 *
				 * @function openItem
				 *
				 * @param {Object} file - Archivo o carpeta seleccionada.
				 * @param {string} file.name - Nombre del elemento.
				 * @param {string} file.type - Tipo ("file" o "folder").
				 *
				 * @returns {void}
				 */
				function openItem(file) {

					/**
					 * Navegación de carpetas
					 */
					if (file.type === "folder") {
						loadFiles(currentPath + "/" + file.name);
					} else {
						/**
						 * Vista previa de archivo
						 */
						preview(file);
					}
				}

				/**
				 * Renderiza la navegación tipo breadcrumb según la ruta actual.
				 *
				 * Ejemplo:
				 * /images/icons → Inicio / images / icons
				 *
				 * Permite navegación rápida a cualquier nivel de la jerarquía.
				 *
				 * @function renderBreadcrumb
				 *
				 * @returns {void}
				 */
				function renderBreadcrumb() {

					const breadcrumb = document.getElementById("breadcrumb");
					breadcrumb.innerHTML = "";

					/**
					 * Divide la ruta actual en segmentos
					 */
					const parts = currentPath.split("/").filter(Boolean);

					let path = "";

					/**
					 * Elemento raíz (Inicio)
					 */
					breadcrumb.innerHTML += `
						<li class="breadcrumb-item">
							<a href="#" onclick="loadFiles(\'\')">
								<i class="bi bi-house-door"></i> Inicio
							</a>
						</li>
					`;

					/**
					 * Construcción dinámica del breadcrumb
					 */
					parts.forEach(p => {
						path += "/" + p;

						breadcrumb.innerHTML += `
							<li class="breadcrumb-item">
								<a href="#" onclick="loadFiles(\'${path}\')">${p}</a>
							</li>
						`;
					});
				}

				/**
				 * Cambia el modo de visualización del explorador.
				 *
				 * Vistas disponibles:
				 * - "grid" → vista tipo tarjetas
				 * - "list" → vista tipo tabla
				 *
				 * @function setView
				 *
				 * @param {string} view - Tipo de vista ("grid" | "list")
				 *
				 * @returns {void}
				 */
				function setView(view) {

					/**
					 * Actualiza estado global
					 */
					currentView = view;

					/**
					 * Alterna visibilidad de vistas usando Bootstrap
					 */
					document.getElementById("gridView")
						.classList.toggle("d-none", view !== "grid");

					document.getElementById("listView")
						.classList.toggle("d-none", view !== "list");
				}

				/**
				 * Formatea el tamaño de un archivo en KB.
				 *
				 * @function formatSize
				 *
				 * @param {number} bytes - Tamaño en bytes.
				 *
				 * @returns {string} Tamaño formateado (ej: "12.5 KB")
				 *
				 * @example
				 * formatSize(2048); // "2.0 KB"
				 */
				function formatSize(bytes) {
					return (bytes / 1024).toFixed(1) + " KB";
				}

				/**
				 * Filtra los archivos según un texto de búsqueda.
				 *
				 * - Búsqueda case-insensitive
				 * - Coincidencia parcial por nombre
				 * - Si no hay query, muestra todos los archivos
				 *
				 * @function filterFiles
				 *
				 * @param {string} query - Texto ingresado por el usuario.
				 *
				 * @returns {void}
				 */
				function filterFiles(query) {

					/**
					 * Normaliza input
					 */
					const q = query.toLowerCase().trim();

					/**
					 * Si está vacío → mostrar todo
					 */
					if (!q) {
						render(allFiles);
						return;
					}

					/**
					 * Filtrado por nombre
					 */
					const filtered = allFiles.filter(file =>
						file.name.toLowerCase().includes(q)
					);

					/**
					 * Render de resultados
					 */
					render(filtered);
				}

				/**
				 * Listener para búsqueda en tiempo real.
				 *
				 * Se ejecuta en cada cambio del input,
				 * aplicando filtro dinámico.
				 */
				document.getElementById("searchInput")
					.addEventListener("input", function () {
						filterFiles(this.value);
					});

				';

				if($escrituraPermitida){
					$widget  .= '
					async function createNewFolder() {
						// Lanzamos el diálogo de SweetAlert2
						const { value: folderName } = await Swal.fire({
							title: "Nueva Carpeta",
							input: "text",
							inputLabel: "Introduce el nombre de la carpeta:",
							inputPlaceholder: "Ej: Vacaciones 2026",
							showCancelButton: true,
							confirmButtonText: "<i class=\'bi bi-check-circle\'></i> Crear",
							cancelButtonText: "<i class=\'bi bi-x-circle\'></i> Cancelar",
							confirmButtonColor: "#81A1C1",
							cancelButtonColor: "#EA5757",
							reverseButtons: true,
							inputValidator: (value) => {
								if (!value || value.trim() === "") {
									return "¡Necesitas escribir un nombre!";
								}
							}
						});

						// Si el usuario canceló o cerró el diálogo, folderName será undefined
						if (!folderName) return;

						// Preparamos los datos
						const formData = new FormData();
						formData.append("SubRoute", '.$SubRouteJS.');
						formData.append("AccessScope", "'.$AccessScope.'");
						formData.append("path", currentPath);
						formData.append("name", folderName.trim());

						try {
							const res = await fetch(`'.$BASE.'/core/fileExplorer/createFolder`, {
								method: "POST",
								headers: { "X-CSRF-Token": (window.CSRF_TOKEN || "") },
								body: formData
							});
							const result = await res.json();
							if (result.success) {
								// Notificación de éxito
								loadFiles(currentPath);
							} else {
								// Notificación de error del servidor
								Swal.fire({position: "top-end",timer: 5000,showConfirmButton: false,timerProgressBar: true,icon: "error",text: result.message || "No se pudo crear la carpeta."});
							}
						} catch (error) {
							console.error("Error al crear carpeta:", error);
							Swal.fire({position: "top-end",timer: 5000,showConfirmButton: false,timerProgressBar: true,icon: "error",text: "Hubo un problema al comunicarse con el servidor."});
						}
					}';
				}
				if($borradoPermitido){
					$widget  .= '
					function deleteFolder(folderName) {

						Swal.fire({
							title: "¿Eliminar carpeta?",
							text: `Se eliminará la carpeta "${folderName}" y todo su contenido.`,
							icon: "warning",
							showCancelButton: true,
							confirmButtonText: "<i class=\'bi bi-trash\'></i> Eliminar",
							cancelButtonText: "<i class=\'bi bi-x-circle\'></i> Cancelar",
							confirmButtonColor: "#81a1c1",
							cancelButtonColor: "#ea5757",
							reverseButtons: true,
							focusCancel: true
						}).then((result) => {

							// Si cancela, salimos
							if (!result.isConfirmed) return;

							const formData = new FormData();
							formData.append("SubRoute", '.$SubRouteJS.');
							formData.append("path", currentPath);
							formData.append("AccessScope", ".$AccessScope.");
							formData.append("name", folderName);

							fetch(`'.$BASE.'/core/fileExplorer/deleteFolder`, {
								method: "POST",
								headers: { "X-CSRF-Token": (window.CSRF_TOKEN || "") },
								body: formData
							})
							.then(res => res.json())
							.then(data => {

								if (data.success) {
									Swal.fire({
										position: "top-end",
										icon: "success",
										text: "La carpeta se eliminó correctamente.",
										timer: 4000,
										showConfirmButton: false,
										timerProgressBar: true
									});

									loadFiles(currentPath);

								} else {
									Swal.fire({
										position: "top-end",
										icon: "error",
										text: data.message || "No se pudo eliminar la carpeta.",
										timer: 4000,
										showConfirmButton: false,
										timerProgressBar: true
									});
								}

							})
							.catch(error => {
								console.error("Error al eliminar carpeta:", error);

								Swal.fire({
									position: "top-end",
									icon: "error",
									text: "Error de comunicación con el servidor.",
									timer: 4000,
									showConfirmButton: false,
									timerProgressBar: true
								});
							});

						});
					}';
				}
				if($escrituraPermitida){
					$widget  .= '
					async function uploadFile(input) {
						if (input.files.length === 0) return;

						const file = input.files[0];
						const formData = new FormData();
						formData.append("SubRoute", '.$SubRouteJS.');
						formData.append("AccessScope", "'.$AccessScope.'");
						formData.append("file", file);
						formData.append("path", currentPath);

						try {
							// Mostramos un feedback visual simple
							const btn = document.querySelector(\'button[onclick*="fileInput"]\');
							const originalText = btn.innerHTML;
							btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Subiendo...`;
							btn.disabled = true;

							const res = await fetch(`'.$BASE.'/core/fileExplorer/uploadFile`, {
								method: "POST",
								headers: { "X-CSRF-Token": (window.CSRF_TOKEN || "") },
								body: formData
							});

							const result = await res.json();
							btn.innerHTML = originalText;
							btn.disabled = false;

							if (result.success) {
								loadFiles(currentPath);
								input.value = ""; // Reset input
							} else {
								Swal.fire({
									position: "top-end",
									icon: "error",
									text: result.message || "No se pudo subir el archivo.",
									timer: 4000,
									showConfirmButton: false,
									timerProgressBar: true
								});
							}
						} catch (error) {
							Swal.fire({
								position: "top-end",
								icon: "error",
								text: "Error de conexión al subir archivo.",
								timer: 4000,
								showConfirmButton: false,
								timerProgressBar: true
							});
						}
					}';
				}
				if($borradoPermitido){
					$widget  .= '
					function deleteFile(fileName) {

						Swal.fire({
							title: "¿Eliminar archivo?",
							text: `Se eliminará el archivo "${fileName}".`,
							icon: "warning",
							showCancelButton: true,
							confirmButtonText: "<i class=\'bi bi-trash\'></i> Eliminar",
							cancelButtonText: "<i class=\'bi bi-x-circle\'></i> Cancelar",
							confirmButtonColor: "#81a1c1",
							cancelButtonColor: "#ea5757",
							reverseButtons: true,
							focusCancel: true
						}).then((result) => {

							// Si cancela, salimos
							if (!result.isConfirmed) return;

							const formData = new FormData();
							formData.append("SubRoute", '.$SubRouteJS.');
							formData.append("path", currentPath);
							formData.append("AccessScope", ".$AccessScope.");
							formData.append("name", fileName);

							fetch(`'.$BASE.'/core/fileExplorer/deleteFile`, {
								method: "POST",
								headers: { "X-CSRF-Token": (window.CSRF_TOKEN || "") },
								body: formData
							})
							.then(res => res.json())
							.then(data => {

								if (data.success) {
									Swal.fire({
										position: "top-end",
										icon: "success",
										text: "El archivo se eliminó correctamente.",
										timer: 4000,
										showConfirmButton: false,
										timerProgressBar: true
									});

									loadFiles(currentPath);

								} else {
									Swal.fire({
										position: "top-end",
										icon: "error",
										text: data.message || "No se pudo eliminar el archivo.",
										timer: 4000,
										showConfirmButton: false,
										timerProgressBar: true
									});
								}

							})
							.catch(error => {
								console.error("Error al eliminar archivo:", error);

								Swal.fire({
									position: "top-end",
									icon: "error",
									text: "Error de comunicación con el servidor.",
									timer: 4000,
									showConfirmButton: false,
									timerProgressBar: true
								});
							});

						});
					}';
				}

				$widget  .= '

				/**
				 * ===================================================================================
				 * PREVIEW REAL
				 * ===================================================================================
				 */
				function preview(file) {
					const modal    = new bootstrap.Modal(document.getElementById("previewModal"));
					const body     = document.getElementById("previewBody");
					const actions  = document.getElementById("previewActions"); // NUEVO contenedor de acciones
					const filePath = '. $rootPathJS . ' + `${currentPath}/${file.name}`;
					const name     = file.name.toLowerCase();

					document.getElementById("previewTitle").innerText = file.name;

					// Loader inicial
					body.innerHTML = `
						<div class="text-center p-5">
							<div class="spinner-border text-primary"></div>
							<div class="mt-3 text-muted">Cargando preview...</div>
						</div>
					`;

					// BOTÓN DESCARGA GLOBAL
					actions.innerHTML = `
						<a href="${normalizarURL(filePath)}" class="btn btn-success" download>
							<i class="bi bi-download"></i> Descargar
						</a>
					`;

					/* ===============================
					* IMÁGENES
					* =============================== */
					if (name.match(/\.(jpg|jpeg|png|gif|webp|svg|bmp)$/)) {
						body.innerHTML = `
							<div class="text-center">
								<img src="${normalizarURL(filePath)}" class="img-fluid rounded shadow">
							</div>
						`;
					}

					/* ===============================
					* PDF
					* =============================== */
					else if (name.endsWith(".pdf")) {
						body.innerHTML = `
							<iframe src="${normalizarURL(filePath)}" width="100%" height="600px" style="border:none;"></iframe>
						`;
					}

					/* ===============================
					* VIDEO
					* =============================== */
					else if (name.match(/\.(mp4|webm|ogg|mov|mkv)$/)) {
						body.innerHTML = `
							<video controls class="w-100 rounded shadow">
								<source src="${normalizarURL(filePath)}">
								Tu navegador no soporta video.
							</video>
						`;
					}

					/* ===============================
					* AUDIO
					* =============================== */
					else if (name.match(/\.(mp3|wav|ogg|aac|flac)$/)) {
						body.innerHTML = `
							<div class="p-4">
								<audio controls class="w-100">
									<source src="${normalizarURL(filePath)}">
									Tu navegador no soporta audio.
								</audio>
							</div>
						`;
					}

					/* ===============================
					* TEXTO / CÓDIGO
					* =============================== */
					else if (name.match(/\.(txt|json|js|css|html|md|xml|csv|log|env|ini)$/)) {
						fetch(normalizarURL(filePath))
							.then(res => res.text())
							.then(text => {
								body.innerHTML = `
									<pre style="
										text-align:left;
										max-height:600px;
										overflow:auto;
										background:#0f172a;
										color:#e2e8f0;
										padding:15px;
										border-radius:10px;
										font-size:13px;
									">${escapeHtml(text)}</pre>
								`;
							})
							.catch(() => {
								body.innerHTML = `<p class="text-danger">Error al cargar archivo</p>`;
							});
					}

					/* ===============================
					* HTML (SANDBOX SEGURO)
					* =============================== */
					else if (name.endsWith(".html")) {
						body.innerHTML = `
							<iframe src="${normalizarURL(filePath)}" width="100%" height="600px" sandbox></iframe>
						`;
					}

					/* ===============================
					* DOCUMENTOS OFFICE
					* =============================== */
					else if (name.match(/\.(doc|docx|xls|xlsx|ppt|pptx)$/)) {
						body.innerHTML = `
							<iframe
								src="https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(location.origin + normalizarURL(filePath))}"
								width="100%"
								height="600px"
								style="border:none;">
							</iframe>
						`;
					}

					/* ===============================
					* ARCHIVOS COMPRIMIDOS
					* =============================== */
					else if (name.match(/\.(zip|rar|7z|tar|gz)$/)) {
						body.innerHTML = `
							<div class="p-5 text-center">
								<i class="bi bi-file-zip fs-1 text-warning"></i>
								<p class="mt-3">Archivo comprimido</p>
								<small class="text-muted">No se puede previsualizar, pero puedes descargarlo</small>
							</div>
						`;
					}

					/* ===============================
					* FALLBACK UNIVERSAL
					* =============================== */
					else {
						body.innerHTML = `
							<div class="p-5 text-center">
								<i class="bi bi-file-earmark fs-1 text-muted"></i>
								<p class="mt-3">Vista previa no disponible</p>
								<small class="text-muted">Tipo de archivo no soportado</small>
							</div>
						`;
					}

					modal.show();
				}

				loadFiles();

			</script>

		';

		/**********************/
		//Imprimir dato
		echo $widget;

	}





}
