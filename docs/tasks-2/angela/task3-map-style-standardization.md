# TASK3 — Angela: Shared Map Style

The local dispatcher and volunteer maps use the TileServer `positron` style by
default. This keeps the basemap neutral and consistent between developers;
incident status colors and local reference layers remain controlled by the
application.

Developers using an existing `.env` should set:

`LOCAL_MAP_STYLE_URL=http://localhost:8080/styles/positron/style.json`

The TileServer must expose the `positron` style at that URL. If the local
TileServer is unavailable, the map displays its existing availability message.
