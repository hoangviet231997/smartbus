# INTRODUCTION

This repository contains SMARTBUS web service (API) and web administration GUI source code.

# INSTALLATION

## Requirements

* NodeJS
* Yarn package manager
* Composer
* PHP >= 7.1

## Cloning the repos.

## Retrieve vendor packages

## Creating a database

## Creating a dotEnv file

## Execute DB migrations

# DEVELOPMENT

Execute:

```
yarn start
```

to start Laravel server and auto watch & build angular process

# TROUBLESHOOTING

## Linux file modifications watch issues

Run:

```
echo fs.inotify.max_user_watches=524288 | sudo tee /etc/sysctl.d/40-max-user-watches.conf && sudo sysctl --system
```
