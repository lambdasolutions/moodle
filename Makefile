all: build

clean:
	fakeroot make -f debian/rules clean

build:
	debian/gen_install
	dpkg-buildpackage -rfakeroot -us -uc -b -tc
	rm debian/install

debug:
	dpkg-buildpackage -rfakeroot -us -uc -b

.PHONY: build
