#!/bin/sh
set -eu

if [ -f docker/compose.override.yaml ]; then
    exec docker compose --project-directory . \
        -f docker/compose.yaml \
        -f docker/compose.override.yaml "$@"
else
    exec docker compose --project-directory . \
        -f docker/compose.yaml "$@"
fi
