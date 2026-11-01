# Development Node.js Runtime

The Laravel frontend and local TileServer use Node.js 22 LTS. The TileServer
depends on a native `sqlite3` binding that is not available in the current
Node.js 24 setup on Windows.

After pulling changes, use Node 22 and reinstall JavaScript dependencies once:

```powershell
cd backend
nvm use 22
Remove-Item -Recurse -Force node_modules
npm ci
```

Without nvm, install Node.js 22 LTS manually before running `npm ci`. Start the
local map server from the `backend` directory with:

```powershell
npx tileserver-gl-light public/maps/osm-2020-02-10-v3.11_philippines_cebu.mbtiles --port 8080
```
