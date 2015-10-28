<?php
namespace History\Services\Internals;

use History\TestCase;

class MailingListArticleCleanerTest extends TestCase
{
    public function testCanParseArticleFromMailingList()
    {
        $article = <<<'TEXT'
Newsgroups: php.internals
Path: news.php.net
Xref: news.php.net php.internals:88938
Return-Path: <php@tutteli.ch>
Mailing-List: contact internals-help@lists.php.net; run by ezmlm
Delivered-To: mailing list internals@lists.php.net
Received: (qmail 4594 invoked from network); 26 Oct 2015 10:10:56 -0000
Received: from unknown (HELO lists.php.net) (127.0.0.1)
  by localhost with SMTP; 26 Oct 2015 10:10:56 -0000
Authentication-Results: pb1.pair.com smtp.mail=php@tutteli.ch; spf=fail; sender-id=fail
Authentication-Results: pb1.pair.com header.from=php@tutteli.ch; sender-id=fail
Received-SPF: fail (pb1.pair.com: domain tutteli.ch does not designate 80.74.154.80 as permitted sender)
X-PHP-List-Original-Sender: php@tutteli.ch
X-Host-Fingerprint: 80.74.154.80 hyperion2.kreativmedia.ch Linux 2.6
Received: from [80.74.154.80] ([80.74.154.80:41467] helo=hyperion2.kreativmedia.ch)
	by pb1.pair.com (ecelerity 2.1.1.9-wez r(12769M)) with ESMTP
	id 34/50-02627-E2CFD265 for <internals@lists.php.net>; Mon, 26 Oct 2015 05:10:56 -0500
Received: from RoLaptop (adsl-84-226-55-217.adslplus.ch [84.226.55.217])
	by hyperion2.kreativmedia.ch (Postfix) with ESMTPSA id B82DBDCE809A;
	Mon, 26 Oct 2015 11:10:50 +0100 (CET)
To: "'Larry Garfield'" <larry@garfieldtech.com>,
	<internals@lists.php.net>
References: <0A.C2.33697.6AECE165@pb1.pair.com> <11.10.09496.8F410265@pb1.pair.com> <001b01d107f1$9d672370$d8356a50$@tutteli.ch> <CAAyV7nG9UyDr1AA5xEK1DYnQ7mLrX95ZNWoZWp421KuUscBmYA@mail.gmail.com> <004101d10844$55f89d90$01e9d8b0$@tutteli.ch> <562AB638.1090304@garfieldtech.com>
In-Reply-To: <562AB638.1090304@garfieldtech.com>
Date: Mon, 26 Oct 2015 11:10:47 +0100
Message-ID: <00e801d10fd6$95b3c6a0$c11b53e0$@tutteli.ch>
MIME-Version: 1.0
Content-Type: text/plain;
	charset="UTF-8"
Content-Transfer-Encoding: quoted-printable
X-Mailer: Microsoft Outlook 14.0
Thread-Index: AQIlm6HitWabnDu1qXRDdKGyTlYfFAGnR/YjAXC9KW0A7mbEgQH3VZCOAjVnauCdktTSYA==
Content-Language: de-ch
Subject: =?UTF-8?Q?AW:_AW:_=5BPHP-DEV=5D_Re:_=5BRFC=5D_Void?=
	=?UTF-8?Q?_Return_Type_=28v0.2=2C_re=C3=B6pening=29?=
From: php@tutteli.ch ("Robert Stoll")

Hi Larry,

> That wouldn't help either, I think.  Then you'd need a separate=
partialLeft(callable:int $cb), partialLeft(callable:string $cb),
> partialLeft(callable:float $cb), and partialLeft(callable:void $db).=
And likely others. That seems exactly like what Anthony
> wants to avoid (rightly).
>
> Indirect calls to arbitrary functions does mean that they need to be=
able to behave consistently when referred to
> abstractly.  Vis, any approach that involves:
>
> function foo() : void {}
> $a =3D foo();
>
> triggering an error condition would make life drastically more=
difficult for higher order function operations like partials or
> memoization.  That seems doubleplusungood.
>
> One way around that would be to only trigger that behavior on a static=
call, not a call to a variable function, but I have no
> idea if that's at all feasible in the engine.  I suspect it's more=
feasible than detecting the function wrapping and only erroring
> at the top level caller, but now I'm just talking out of my butt. :-)
>
> That leaves "documentation of intent for the developer" (which is a=
valid argument) and "slap someone's hand for
> returning non-null inside the function itself" (which is valid, but=
leaves the question of whether return null should error).
>
> --Larry Garfield
>
> --

Well, it really depends on the use case. I would probably not write such=
a generic partial function, especially not allowing functions which=
return a value and others which don't. I could imagine to use=
callable:mixed to allow values of arbitrary types to be returned. Yet,=
that would still not include a function which does not return a value.=
IMO we do not really proceed further with this RFC when discussing this=
special case - callable:int, callable:mixed etc is not supported by PHP=
now, so we should focus on the essential now.

IMO we should get an agreement what void means in PHP, I see the=
following options:

1 void is a type with a value set containing null (ergo corresponds to=
the set { null }) and hence it is perfectly fine to return null from a=
function with return type void (naming it void is controversial among=
the list -- others prefer null -- but that can be discussed further=
afterwards)
2 void is a type with an empty value set (a special value respectively)=
and hence one cannot return null from a function with return type void=
and
  a) such a function returns null implicitly nonetheless
  b) such a function returns a special value which triggers a fatal=
error when it is accessed

IMO 2a is inconsistent and I would only consider 1 or 2b

.
TEXT;
        $cleaner = new MailingListArticleCleaner();
        $cleaned = $cleaner->cleanup(explode(PHP_EOL, $article));

        $this->assertEquals(<<<'TEXT'
Hi Larry,

> That wouldn't help either, I think.  Then you'd need a separate
partialLeft(callable:int $cb), partialLeft(callable:string $cb),
> partialLeft(callable:float $cb), and partialLeft(callable:void $db).
And likely others. That seems exactly like what Anthony
> wants to avoid (rightly).
>
> Indirect calls to arbitrary functions does mean that they need to be
able to behave consistently when referred to
> abstractly.  Vis, any approach that involves:
>
> function foo() : void {}
> $a = foo();
>
> triggering an error condition would make life drastically more
difficult for higher order function operations like partials or
> memoization.  That seems doubleplusungood.
>
> One way around that would be to only trigger that behavior on a static
call, not a call to a variable function, but I have no
> idea if that's at all feasible in the engine.  I suspect it's more
feasible than detecting the function wrapping and only erroring
> at the top level caller, but now I'm just talking out of my butt. :-)
>
> That leaves "documentation of intent for the developer" (which is a
valid argument) and "slap someone's hand for
> returning non-null inside the function itself" (which is valid, but
leaves the question of whether return null should error).
>
> --Larry Garfield
>
> --

Well, it really depends on the use case. I would probably not write such
a generic partial function, especially not allowing functions which
return a value and others which don't. I could imagine to use
callable:mixed to allow values of arbitrary types to be returned. Yet,
that would still not include a function which does not return a value.
IMO we do not really proceed further with this RFC when discussing this
special case - callable:int, callable:mixed etc is not supported by PHP
now, so we should focus on the essential now.

IMO we should get an agreement what void means in PHP, I see the
following options:

1 void is a type with a value set containing null (ergo corresponds to
the set { null }) and hence it is perfectly fine to return null from a
function with return type void (naming it void is controversial among
the list -- others prefer null -- but that can be discussed further
afterwards)
2 void is a type with an empty value set (a special value respectively)
and hence one cannot return null from a function with return type void
and
  a) such a function returns null implicitly nonetheless
  b) such a function returns a special value which triggers a fatal
error when it is accessed

IMO 2a is inconsistent and I would only consider 1 or 2b
TEXT
            , $cleaned);
    }

    public function testCanRespectBoundaries()
    {
        $article = <<<'TEXT'
Newsgroups: php.internals
Path: news.php.net
Xref: news.php.net php.internals:78984
Return-Path: <tyra3l@gmail.com>
Mailing-List: contact internals-help@lists.php.net; run by ezmlm
Delivered-To: mailing list internals@lists.php.net
Received: (qmail 5683 invoked from network); 19 Nov 2014 08:46:08 -0000
Received: from unknown (HELO lists.php.net) (127.0.0.1)
  by localhost with SMTP; 19 Nov 2014 08:46:08 -0000
Authentication-Results: pb1.pair.com header.from=tyra3l@gmail.com; sender-id=pass
Authentication-Results: pb1.pair.com smtp.mail=tyra3l@gmail.com; spf=pass; sender-id=pass
Received-SPF: pass (pb1.pair.com: domain gmail.com designates 209.85.214.170 as permitted sender)
X-PHP-List-Original-Sender: tyra3l@gmail.com
X-Host-Fingerprint: 209.85.214.170 mail-ob0-f170.google.com
Received: from [209.85.214.170] ([209.85.214.170:50473] helo=mail-ob0-f170.google.com)
    by pb1.pair.com (ecelerity 2.1.1.9-wez r(12769M)) with ESMTP
    id 7B/03-15277-CC85C645 for <internals@lists.php.net>; Wed, 19 Nov 2014 03:46:05 -0500
Received: by mail-ob0-f170.google.com with SMTP id wp18so85267obc.29
        for <internals@lists.php.net>; Wed, 19 Nov 2014 00:46:01 -0800 (PST)
DKIM-Signature: v=1; a=rsa-sha256; c=relaxed/relaxed;
        d=gmail.com; s 120113;
        h=mime-version:in-reply-to:references:date:message-id:subject:from:to
         :cc:content-type;
        bh=SHTwGXVyRiFBp5eDUjF1KB2BJg+oR63Ux0JBjjsLwms=;
        b=gm7U//CXLdrS6yvml0MHSjb1LAQPeHlaxnJcS313T7U0iuf5VDT8VsE7VUtKhhtqD4
         08LQBMtH5cJ/5Qd5RekZa03j6x6am5CE3z+f3t+8W/fsHfoHeIa3GzBgtX3/ec0RLgz1
         GRq6OaIHa9AW3DlOpgR1RnAfv13Fb8tRfgGtPdwvEX2QgD42DuPiLmCKW+eMnGv7VqP+
         Yx41a5H4536zrAI7opYAediptvK2eKCF669WAnBMgqQ1ureK+FuVVPlFq36g4fCpj6QF
         EPrH0LrDNDFKG08H3M8udzea3orHmGDPfI3h4sf2ch3zxNt2Rwma0rtLpr5bbG5vLTyq
         bDeg==
MIME-Version: 1.0
X-Received: by 10.60.115.227 with SMTP id jr3mr13541195oeb.33.1416386761676;
 Wed, 19 Nov 2014 00:46:01 -0800 (PST)
Received: by 10.60.79.131 with HTTP; Wed, 19 Nov 2014 00:46:01 -0800 (PST)
In-Reply-To: <CAFMT4NpJxuuqc-wTr1aE4K+wZBT8+04j+9-SsY+nhUMU83fDUg@mail.gmail.com>
References: <CAFMT4NpJxuuqc-wTr1aE4K+wZBT8+04j+9-SsY+nhUMU83fDUg@mail.gmail.com>
Date: Wed, 19 Nov 2014 09:46:01 +0100
Message-ID: <CAH-PCH47Mx9AiP9yWLK+_nv8hpWHyXDjnn-h0sk66gPFOpRPxA@mail.gmail.com>
To: Levi Morrison <levim@php.net>
Cc: internals <internals@lists.php.net>
Content-Type: multipart/alternative; boundary=089e01184242cb60600508323dec
Subject: Re: [PHP-DEV] [RFC] Remove PHP 4 Constructors
From: tyra3l@gmail.com (Ferenc Kovacs)

--089e01184242cb60600508323dec
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: quoted-printable

On Wed, Nov 19, 2014 at 12:11 AM, Levi Morrison <levim@php.net> wrote:

> Dear Internals,
>
> I am proposing an RFC[1] to remove PHP 4 constructors in PHP 7. If
> accepted, methods with the same name as their defining class will no
> longer be recognized as constructors. As noted in the RFC, there are
> already many situations where we do not recognize these methods as
> constructors, such as in namespaces and traits and when `function
> __construct` is also present.
>
> Andrea Faulds has kindly written a utility that identifies when a PHP
> 4 constructor is defined[2]. It does not automatically change the code
> for liability reasons. The utility PHPMD[3] can also detect this but
> has a false positive when `__construct` is also defined.
>
> Cheers,
> Levi Morrison
>
>
>   [1]: https://wiki.php.net/rfc/remove_php4_constructors
>   [2]: https://github.com/TazeTSchnitzel/PHP4_Constructor_Finder
>   [3]:
> http://phpmd.org/rules/naming.html#constructorwithnameasenclosingclass
>
> --
> PHP Internals - PHP Runtime Development Mailing List
> To unsubscribe, visit: http://www.php.net/unsub.php
>
>
+1 from me.

--
Ferenc Kov=C3=A1cs
@Tyr43l - http://tyrael.hu

--089e01184242cb60600508323dec--
TEXT;
        $cleaner = new MailingListArticleCleaner();
        $cleaned = $cleaner->cleanup(explode(PHP_EOL, $article));

        $this->assertEquals(<<<'TEXT'
On Wed, Nov 19, 2014 at 12:11 AM, Levi Morrison <levim@php.net> wrote:

> Dear Internals,
>
> I am proposing an RFC[1] to remove PHP 4 constructors in PHP 7. If
> accepted, methods with the same name as their defining class will no
> longer be recognized as constructors. As noted in the RFC, there are
> already many situations where we do not recognize these methods as
> constructors, such as in namespaces and traits and when `function
> __construct` is also present.
>
> Andrea Faulds has kindly written a utility that identifies when a PHP
> 4 constructor is defined[2]. It does not automatically change the code
> for liability reasons. The utility PHPMD[3] can also detect this but
> has a false positive when `__construct` is also defined.
>
> Cheers,
> Levi Morrison
>
>
>   [1]: https://wiki.php.net/rfc/remove_php4_constructors
>   [2]: https://github.com/TazeTSchnitzel/PHP4_Constructor_Finder
>   [3]:
> http://phpmd.org/rules/naming.html#constructorwithnameasenclosingclass
>
> --
> PHP Internals - PHP Runtime Development Mailing List
> To unsubscribe, visit: http://www.php.net/unsub.php
>
>
+1 from me
TEXT
            , $cleaned);
    }

    public function testCanParseSpecialBoundaries()
    {
        $article = <<<'TEXT'
Newsgroups: php.internals
Path: news.php.net
Xref: news.php.net php.internals:68280
Return-Path: <indeyets@gmail.com>
Mailing-List: contact internals-help@lists.php.net; run by ezmlm
Delivered-To: mailing list internals@lists.php.net
Received: (qmail 30731 invoked from network); 23 Jul 2013 06:21:10 -0000
Received: from unknown (HELO lists.php.net) (127.0.0.1)
  by localhost with SMTP; 23 Jul 2013 06:21:10 -0000
Authentication-Results: pb1.pair.com smtp.mail=indeyets@gmail.com; spf=pass; sender-id=pass
Authentication-Results: pb1.pair.com header.from=indeyets@gmail.com; sender-id=pass
Received-SPF: pass (pb1.pair.com: domain gmail.com designates 209.85.215.42 as permitted sender)
X-PHP-List-Original-Sender: indeyets@gmail.com
X-Host-Fingerprint: 209.85.215.42 mail-la0-f42.google.com
Received: from [209.85.215.42] ([209.85.215.42:49262] helo=mail-la0-f42.google.com)
    by pb1.pair.com (ecelerity 2.1.1.9-wez r(12769M)) with ESMTP
    id B6/C5-17597-4D02EE15 for <internals@lists.php.net>; Tue, 23 Jul 2013 02:21:08 -0400
Received: by mail-la0-f42.google.com with SMTP id eh20so3556217lab.29
        for <internals@lists.php.net>; Mon, 22 Jul 2013 23:21:05 -0700 (PDT)
DKIM-Signature: v=1; a=rsa-sha256; c=relaxed/relaxed;
        d=gmail.com; s 120113;
        h=content-type:mime-version:subject:from:in-reply-to:date:cc
         :message-id:references:to:x-mailer;
        bh=6IynTZrg4zGzf5VZ2IxUmJuv0ppNZqfi2fX0YZ2UbxE=;
        b=eBJsCbUXf8kbKhzElGP5KmrCuJLFC5mNjjEf+ehfqpOb63Opb+fAKyXHOMR2ORrWVG
         36SEjQWSsUR0tR0nu9JBQbxvG/d8xdwYCPvIgHDY2Iw/6ATp9BmnB7Xl+1lm5KQAMhDS
         Z8ippgk6AtMzMykgtunp20lxExDMKsGDLfIciNqQR3agHorV5h0mFKjlbvWy5iKMy6PC
         0X3wABzyrxUmD96y8VvlnNUy/GvbaJ4aHfA3FD7m1wy/H8wN7+59jQA/KlUDtT1Atw5J
         FUR3UZgRZJcrEVFkU1RpCRi/WEWirJGcA/JVKkNCGrT9zEgJvtnSp22FMNIJrmYaizEi
         /HMw==
X-Received: by 10.112.164.164 with SMTP id yr4mr13766710lbb.88.1374560465032;
        Mon, 22 Jul 2013 23:21:05 -0700 (PDT)
Received: from [10.0.1.7] ([46.252.161.106])
        by mx.google.com with ESMTPSA id 8sm12126637lbq.4.2013.07.22.23.21.02
        for <multiple recipients>
        (version=TLSv1 cipher=ECDHE-RSA-RC4-SHA bits=128/128);
        Mon, 22 Jul 2013 23:21:03 -0700 (PDT)
Content-Type: multipart/signed; boundary="Apple-Mail=_C7FC6D38-61D9-4703-A5C5-F7C3C16B54CD"; protocol="application/pgp-signature"; micalg=pgp-sha512
Mime-Version: 1.0 (Mac OS X Mail 6.5 \(1508\))
In-Reply-To: <60BF8DD5-FEEA-47D9-834F-6C7FDEF3B879@wiedler.ch>
Date: Tue, 23 Jul 2013 10:20:51 +0400
Cc: "internals@lists.php.net" <internals@lists.php.net>
Message-ID: <A61F400D-AC02-4F79-8703-A42C96E30692@gmail.com>
References: <60BF8DD5-FEEA-47D9-834F-6C7FDEF3B879@wiedler.ch>
To: Igor Wiedler <igor@wiedler.ch>
X-Mailer: Apple Mail (2.1508)
Subject: Re: [PHP-DEV] [RFC] Importing namespaced functions
From: indeyets@gmail.com (Alexey Zakhlestin)

--Apple-Mail=_C7FC6D38-61D9-4703-A5C5-F7C3C16B54CD
Content-Transfer-Encoding: quoted-printable
Content-Type: text/plain;
    charset=us-ascii

On 19.07.2013, at 21:29, Igor Wiedler <igor@wiedler.ch> wrote:

> Hello internals,
>
> I posted the initial idea for a use_function RFC a few months back. I=
would like to make the proposal official now, and open it for=
discussion.
>
> I also did some work on a patch that probably still has some issues.=
Review on that is welcome as well.
>
> RFC: https://wiki.php.net/rfc/use_function
> Patch: https://github.com/php/php-src/pull/388


I don't see much space for discussion :)
It's a very useful addition with nice explicit semantics and a small=
patch.

+1

--
Alexey Zakhlestin
CTO at Grids.by/you
https://github.com/indeyets
PGP key: http://indeyets.ru/alexey.zakhlestin.pgp.asc




--Apple-Mail=_C7FC6D38-61D9-4703-A5C5-F7C3C16B54CD
Content-Transfer-Encoding: 7bit
Content-Disposition: attachment;
    filename=signature.asc
Content-Type: application/pgp-signature;
    name=signature.asc
Content-Description: Message signed with OpenPGP using GPGMail

-----BEGIN PGP SIGNATURE-----
Version: GnuPG/MacGPG2 v2.0.19 (Darwin)

iQEcBAEBCgAGBQJR7iDNAAoJEMkJcRxZdR27VmEIAJWCT0vxKRIWW0yWBHBYKXAT
83UhEUjx2JdM5fzl8qHQFouyeEmIw8i+duBV/80OW+o8pHlOl9AuLWAOVBv0FH8D
OvLo7o/+UvJksZYxCa4cF95rlrY+Nv6T8h9ic8c+y/Yt4KmaeOwzf0EC3X/7YfIO
W0HokSvwi3kVgNNfJcC0Y4h25qmovOZm9qTtsauVa35VBkV8VwFtd81CjeoN7b9a
v6Gd3x1aVY5IPfXf6vL8zWCagH7ehvxwo+vn2dXdKuvh4T2bqddo66+WQd0YI1SP
Dmpl+v0REP6vsyxxmclNNBwFg8A84RcQ6jGjitlboFjkvYuSxRWKhpu86im5NyQ=
=qVkH
-----END PGP SIGNATURE-----

--Apple-Mail=_C7FC6D38-61D9-4703-A5C5-F7C3C16B54CD--
TEXT;
        $cleaner = new MailingListArticleCleaner();
        $cleaned = $cleaner->cleanup(explode(PHP_EOL, $article));

        $this->assertEquals(<<<'TEXT'
On 19.07.2013, at 21:29, Igor Wiedler <igor@wiedler.ch> wrote:

> Hello internals,
>
> I posted the initial idea for a use_function RFC a few months back. I
would like to make the proposal official now, and open it for
discussion.
>
> I also did some work on a patch that probably still has some issues.
Review on that is welcome as well.
>
> RFC: https://wiki.php.net/rfc/use_function
> Patch: https://github.com/php/php-src/pull/388


I don't see much space for discussion :)
It's a very useful addition with nice explicit semantics and a small
patch.

+1
TEXT
            , $cleaned);
    }

    public function testProperlyReplacesAllBoundaries()
    {

        $article = <<<'TEXT'
Newsgroups: php.internals
Path: news.php.net
Xref: news.php.net php.internals:69847
Return-Path: <nikita.ppv@gmail.com>
Mailing-List: contact internals-help@lists.php.net; run by ezmlm
Delivered-To: mailing list internals@lists.php.net
Received: (qmail 37455 invoked from network); 24 Oct 2013 17:41:32 -0000
Received: from unknown (HELO lists.php.net) (127.0.0.1)
  by localhost with SMTP; 24 Oct 2013 17:41:32 -0000
Authentication-Results: pb1.pair.com header.from=nikita.ppv@gmail.com; sender-id=pass
Authentication-Results: pb1.pair.com smtp.mail=nikita.ppv@gmail.com; spf=pass; sender-id=pass
Received-SPF: pass (pb1.pair.com: domain gmail.com designates 209.85.219.48 as permitted sender)
X-PHP-List-Original-Sender: nikita.ppv@gmail.com
X-Host-Fingerprint: 209.85.219.48 mail-oa0-f48.google.com
Received: from [209.85.219.48] ([209.85.219.48:35291] helo=mail-oa0-f48.google.com)
\tby pb1.pair.com (ecelerity 2.1.1.9-wez r(12769M)) with ESMTP
\tid 61/CC-10840-CCB59625 for <internals@lists.php.net>; Thu, 24 Oct 2013 13:41:32 -0400
Received: by mail-oa0-f48.google.com with SMTP id m17so2716562oag.21
        for <internals@lists.php.net>; Thu, 24 Oct 2013 10:41:29 -0700 (PDT)
DKIM-Signature: v=1; a=rsa-sha256; c=relaxed/relaxed;
        d=gmail.com; s=20120113;
        h=mime-version:date:message-id:subject:from:to:content-type;
        bh=CMI6M6gyu/1cMTq2zWnlyQwDohBKDYidVud9SYAS0e8=;
        b=uzVlr30cVswMloi4oRaGrf4ZkuhpZo9pveM+r2k3YG5lcD+ualsvG4XKSQnD3QmMwb
         0NSNkrjiE+FBY+HW+4J/8oZcZE6yGZv7tSwUgvLKD0lMciJSYifCT8kUSSn4uAjgKHBG
         zqqlXpKUTbm2RCWQr+OkIqH7iTfMo7qdEGZZ+fYORpgpKp+BIn+rooIWE2B54gNqQSrd
         wo9PgzyewGbqgry8p1rL2172rS1weGJydxgiLo75KJf5j++eS0ma49fSPBRTPFnlIo8c
         bhOB45c8Tcgn3wlT+2QeWO1ISdmKSWIIEskxH4DVGehGDl2DJgoNiKce2ZfQ8tbghWIf
         SC6A==
MIME-Version: 1.0
X-Received: by 10.182.149.234 with SMTP id ud10mr1698492obb.73.1382636489775;
 Thu, 24 Oct 2013 10:41:29 -0700 (PDT)
Received: by 10.182.54.112 with HTTP; Thu, 24 Oct 2013 10:41:29 -0700 (PDT)
Date: Thu, 24 Oct 2013 19:41:29 +0200
Message-ID: <CAF+90c8OgW4Nonaz+HQn+gi4r+AdaEMXsTxCAhn=WO-+aQQsZQ@mail.gmail.com>
To: PHP internals <internals@lists.php.net>
Content-Type: multipart/alternative; boundary=001a11348918d3a13704e9802459
Subject: [RFC] Exceptions in the engine
From: nikita.ppv@gmail.com (Nikita Popov)

--001a11348918d3a13704e9802459
Content-Type: text/plain; charset=ISO-8859-1

Hi internals!

I'd like to propose an RFC, which allows the use of exceptions within the
engine and also allows changing existing fatal errors to exceptions:

    https://wiki.php.net/rfc/engine_exceptions

This topic has been cropping up in the discussions for several of the
recent RFCs and I think the time has come to consider moving away from
fatal errors.

Thoughts?

Thanks,
Nikita

--001a11348918d3a13704e9802459--
TEXT;
        $cleaner = new MailingListArticleCleaner();
        $cleaned = $cleaner->cleanup(explode(PHP_EOL, $article));

        $this->assertEquals(<<<'TEXT'
Hi internals!

I'd like to propose an RFC, which allows the use of exceptions within the
engine and also allows changing existing fatal errors to exceptions:

    https://wiki.php.net/rfc/engine_exceptions

This topic has been cropping up in the discussions for several of the
recent RFCs and I think the time has come to consider moving away from
fatal errors.

Thoughts?

Thanks,
Nikita
TEXT
            , $cleaned);
    }
}
