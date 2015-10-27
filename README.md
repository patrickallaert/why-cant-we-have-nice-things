# THIS IS A WORK IN PROGRESS AND NOT PUBLIC YET. HERE BE DRAGONS.

# Why can't we have nice things

This is an application to uniformizes PHP internals data in one central place and add insight into it (voting patterns, history, etc.). It aims to unify the PHP wiki, the repository and the mailing list, by displaying a clear interface of who voted on what, the comments on an RFC directly on it, and all kinds of stuff.

## Setup

First copy `.env.example` to `.env` and fill in the informations for your environment. Then install the dependencies and build the assets:

```bash
$ composer install
$ npm install
$ npm run build
```

Then migrate the database:

```bash
$ composer migrate
```

And, well. That's pretty much it.

## Fetching the data

You can run `./console sync` to sync RFCs and users and such. It will be slow the first time and then faster after that.
Data from the mailing list is synced separately for now through the `./console sync:internals` command.

## Running the tests

```bash
$ composer test
```
