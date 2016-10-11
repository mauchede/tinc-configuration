# README

## Installation

```sh
sudo curl -sLo /usr/local/bin/tinc-configuration "https://github.com/mauchede/tinc-configuration/raw/master/dist/tinc-configuration.phar"
sudo chmod +x /usr/local/bin/tinc-configuration
```

## Usage

```sh
# Initialize the configuration of the current host

tinc-configuration init --name nodeA --public-ip 1.1.1.1 --private-cidr 10.0.0.1/32

# Add an external host

cat > /tmp/host.pub << EOF
-----BEGIN RSA PUBLIC KEY-----
MIICCgKCAgEAuupuGT9OKdoUtGG1ouSJYS/9tV7+SVvRClixABWoiZVfMGoNolcs
...
-----END RSA PUBLIC KEY-----
EOF
tinc-configuration host:add --name nodeB --public-ip 2.2.2.2 --private-cidr 10.0.0.2 --public-key /tmp/host.pub

# Connect the current host to an external host

tinc-configuration connection:add --name nodeB

# Analyze the current host

tinc-configuration info

# Analyze an external host

tinc-configuration host:info --name nodeB

# Remove a connection between two hosts

tinc-configuration connection:remove --name nodeB

# Remove an external host

tinc-configuration host:remove --name nodeB
```

## Contributing

1. Fork it.
2. Create your branch: `git checkout -b my-new-feature`.
3. Commit your changes: `git commit -am 'Add some feature'`.
4. Push to the branch: `git push origin my-new-feature`.
5. Submit a pull request.

## Credits

The project has been inspired by [JensErat](https://github.com/JensErat).

## Links

* [JensErat/docker-tinc](https://github.com/JensErat/docker-tinc)
* [tinc](https://www.tinc-vpn.org/)
