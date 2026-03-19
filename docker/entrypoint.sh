#!/bin/bash

# Helper script to avoid git warning about different owners of the project directory.
# When mounting and overriding a folder inside the container, git warns if the current accessing user
# and the user (jsonld) inside the container are not the same (have different IDs). When this happens,
# it might lead to problems when running composer installation.

USER_ID=$(stat -c "%u" /home/jsonld/code)
GROUP_ID=$(stat -c "%g" /home/jsonld/code)

usermod -u $USER_ID jsonld
groupmod -g $GROUP_ID jsonld

su jsonld -c "git config --global --add safe.directory /home/jsonld/code"

exec runuser -u jsonld -- "$@"